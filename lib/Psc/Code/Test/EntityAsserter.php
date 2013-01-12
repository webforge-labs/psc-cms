<?php

namespace Psc\Code\Test;

use Psc\Code\Code;
use Psc\Doctrine\EntityDataRow;
use Psc\Doctrine\Helper as DoctrineHelper;

/**
 *
 * alle assert-Funktionen erhalten 3 Parameter:
 * 1. $expected der Wert der Erwartet wird
 * 2. $property der Name des Propertys oder der Getter der Propertys des Entities. ist dies === NULL wird der dritte Parameter als Wert benutzt
 * 3. $actual der aktuelle Wert, wird nur benutzt wenn der 2. Parameter ($property) === NULL ist
 */
class EntityAsserter extends \Psc\Object {
  
  const TYPE_SAME = 'same';
  const TYPE_EQUALS = 'equals';
  const TYPE_COLLECTION = 'collection';
  
  protected $test;
  
  protected $entity;
  
  public function __construct($entity, \Psc\Code\Test\Base $test) {
    if (!($entity instanceof \Psc\Doctrine\Object) && !($entity instanceof \Psc\Doctrine\Entity)) {
      throw new \InvalidArgumentException('Argument 1 muss Doctrine\Object oder Doctrine\Entity sein');
    }
    $this->entity = $entity;
    $this->test = $test;
  }
  
  /**
   * Vergleicht 2 Werte auf Inhalt
   *
   * wie == 
   * Collections können hiermit nicht verglichen werden
   * dafür equalsCollection oder equalsEntity benutzen
   * @api
   */
  public function equals($expected, $property, $actual = NULL) {
    list ($expected, $actual) = $this->getExpectedActual($expected, $property, $actual);
    
    if ($this->isEntity($expected) && $this->isEntity($actual)) {
      return $this->equalsEntity($expected, $property, $actual);
    }
    
    $this->assertNotBigObjects($expected, $actual, $property);

    $this->test->assertEquals($expected, $actual, 'assertEquals Property: '.$property);
  }

  /**
   * Vergleicht 2 Werte auf Typen + Inhalt
   *
   * wie ===
   * wie equals jedoch vergleicht es die Typen.
   *
   * sind beide Eingaben Entities wird equalsEntity benutzt.
   * @api
   */
  public function same($expected, $property, $actual = NULL) {
    list ($expected, $actual) = $this->getExpectedActual($expected, $property, $actual);
    
    if ($this->isEntity($expected) && $this->isEntity($actual)) {
      return $this->equalsEntity($expected, $property, $actual);
    }
    
    $this->assertNotBigObjects($expected, $actual, $property);
    
    $this->test->assertSame($expected, $actual, 'assertSame Property: '.$property);
  }
  
  /**
   * Vergleicht 2 Entities
   *
   * die Entities werden aufgrund von equals() von \Psc\Doctrine\Object verglichen
   * @api
   */
  public function equalsEntity(\Psc\Doctrine\Object $expectedEntity, $property, \Psc\Doctrine\Object $actualEntity = NULL) {
    if (func_num_args() === 2)
      $actualEntity = $this->getActual($property, 'Psc\Doctrine\Object');
    
    $this->test->assertTrue($expectedEntity->equals($actualEntity), 'assertEntityEquals Property: '.$property); 
  }
  
  /**
   * @api
   */
  public function equalsOrderedCollection($expectedCollection, $property, $actualCollection = NULL) {
    return $this->equalsCollection($expectedCollection, $property, $actualCollection, TRUE);
  }
  
  /**
   * Vergleicht 2 Collections miteinander
   *
   * Die Objekte werden aufgrund der equals() Funktion miteinander verglichen
   * wenn die Reihenfolge überprüft werden soll muss $assertOrder TRUE sein dies ist äquivalent dann zu equalsOrderedCollection()
   * 
   * @api
   */
  public function equalsCollection($expectedCollection, $property, $actualCollection = NULL, $assertOrder = FALSE) {
    if ($expectedCollection instanceof \Doctrine\Common\Collections\Collection) {
      $expectedCollection = $expectedCollection->toArray();
    } elseif(is_array($expectedCollection)) {
      
    } else {
      throw new \Psc\Exception('$expectedCollection muss eine Collection sein. (Property: '.$property.') given: '.Code::varInfo($expectedCollection));
    }
    
    if ($property !== NULL) {
      $actualCollection = $this->getActual($property);
    }
  
    if ($actualCollection instanceof \Doctrine\Common\Collections\Collection) {
      $actualCollection = $actualCollection->toArray();
    } elseif (is_array($actualCollection)) {
    
    } else {
      throw new \Psc\Exception('$actualCollection muss eine Collection sein.'.($property !== NULL? ' (Property: '.$property.')' : NULL));
    }
    
    if (!$assertOrder) {
      sort($expectedCollection);
      sort($actualCollection);
    }
    
    $convertToId = function (\Psc\Doctrine\Object $o) {
      return $o->getIdentifier();
    };
  
    $this->test->assertEquals(array_map($convertToId,$expectedCollection),
                              array_map($convertToId,$actualCollection),
                              sprintf("Collection fuer property '%s' ist nicht mit der erwarteten Collection identisch.\n%s",
                                      $property,
                                      DoctrineHelper::debugCollectionDiff($expectedCollection, $actualCollection)));
  }
  
  /**
   * @return bool
   */
  public function isBigObject($object) {
    if (!is_object($object)) return FALSE;
    
    if ($this->isEntity($object)) return TRUE;
    if ($object instanceof \Doctrine\Common\Collections\Collection) return TRUE;
    
    return FALSE;
  }
  
  public function isEntity($object) {
    return is_object($object) && ($object instanceof \Psc\Doctrine\Object);
  }
  
  /* API Helpers */
  /**
   * Shorthand für Vergleiche
   *
   * @params $properties, $type                     mapsTo:     assertProperties()
   * @params $entityDataRow, $properties, $type     mapsTo:     assertEntityDataRow()
   */
  public function assert($arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL) {
    if (is_array($arg1)) {
      return $this->assertProperties($arg1, $arg2);
    } elseif ($arg1 instanceof EntityDataRow) {
      if (func_num_args() === 4) {
        return $this->assertEntityDataRowSetter($arg1, $arg2, $ar3, $arg4);
      } else {
        return $this->assertEntityDataRow($arg1, (array) $arg2, $arg3);
      }
    }
    
    $signatur = array_map(array('\Psc\Code\Code','getType'), $args);
    throw new \BadMethodCallException('Es wurde kein Konstruktor für die Parameter: '.implode(', ',$signatur).' gefunden');
  }
  
  /*
   * Vergleicht mehrere Properties
   *
   *
   * $this->assert(array('eins'=>'value1', 'zwei'=>'value2', 'drei'=>'value3'), self::TYPE_SAME);
   * 
   * ist äquivalent zu
   * 
   * $this->same('value1', 'eins');
   * $this->same('value2', 'zwei');
   * $this->same('value3', 'drei');
   *
   * @param const $type TYPE_SAME | TYPE_EQUALS
   * @param array $properties Schlüssel sind propertyNamen und Werte sind die Werte für den Parameter $expected
   */
  public function assertProperties(Array $properties, $type = self::TYPE_SAME) {
    if ($type === NULL) $type = self::TYPE_SAME; // nochmal wegen assert() verteiler
    foreach ($properties as $property => $expected) {
      $this->assertProperty($property, $expected, $type);
    }
    
    return $this;
  }
  
  /**
   * Vergleicht genau 1 Property
   *
   * $this->assertProperty('eins', 'value1');
   * ist äquivalent zu:
   * $this->same('value1', 'eins');
   * 
   * $this->assertProperty('eins', 'value1', self::TYPE_EQUALS);
   * ist äquivalent zu:
   * $this->equals('value1', 'eins');
   * 
   * @param string $property
   * @param mixed $expected
   * @param const $type
   */
  public function assertProperty($property, $expected, $type = self::TYPE_SAME) {
    if ($property === NULL) {
      throw new \Psc\Exception('Property darf nicht === NULL sein');
    }
    
    if ($type === self::TYPE_SAME) {
      $this->same($expected, $property);
    } elseif ($type === self::TYPE_EQUALS) {
      $this->equals($expected, $property);
    } elseif ($type === self::TYPE_COLLECTION) {
      $this->equalsCollection($expected, $property);
    } else {
      throw new \InvalidArgumentException('Unbekannter Type: '.Code::varInfo($type));
    }
    
    return $this;
  }
  
  public function assertEntityDataRowGetter(EntityDataRow $row, $property, $getter, $type = self::TYPE_SAME) {
    if (!$row->has($property)) {
      throw new \Psc\Exception('Property: '.$property.' kommt nicht in der EntityDataRow: '.$row->getName().' vor.');
    }
    
    $expected = $row->getData($property);
    $this->assertProperty($getter, $expected, $type);
    return $this;
  }
  
  /**
   * Vergleich bestimmte Properties aus der Row
   * 
   * $this->assertEntityDataRow($row, array('eins','zwei','drei'));
   * ist äquivalent zu
   * $this->assertProperty('eins', $row->getData('eins'), $zuMetaPassenderType);
   *
   * Wenn man alle Properties testen will kann man:
   * $this->assertEntityDataRow($row, $row->getProperties());
   * 
   * machen.
   * - Collections mit META Eintrag in row werden erkannt
   * @param string[] $properties die Namen der Properties aus der $row
   */
  public function assertEntityDataRow(EntityDataRow $row, Array $properties, $type = self::TYPE_SAME) {
    if ($type === NULL) $type = self::TYPE_SAME; // nochmal wegen assert() verteiler
    foreach ($properties as $property) {
      if (mb_strpos($property, ':') !== FALSE) {
        list ($property, $getter) = explode(':',$property);
      } else {
        $getter = $property;
      }
      
      if (!$row->has($property)) {
        throw new \Psc\Exception('Property: '.$property.' kommt nicht in der EntityDataRow: '.$row->getEntityName().' vor.');
      }
      
      $expected = $row->getData($property);
      $meta = $row->getMeta($property);
      
      if ($meta === NULL) {
        $assertionType = $type;
        
      } elseif ($meta === EntityDataRow::TYPE_COLLECTION) {
        $assertionType = self::TYPE_COLLECTION;
      
      } else {
        throw new \Psc\Exception('Property mit unbekanntem Meta in EntityDataRow: '.Code::varInfo($meta));
      }
      
      $this->assertProperty($getter, $expected, $assertionType);
    }
    
    return $this;
  }
  
  public function row(EntityDataRow $row, Array $properties, $type = self::TYPE_SAME) {
    return $this->assertEntityDataRow($row, $properties, $type);
  }
  
  /* Helpers */  
  protected function getExpectedActual($expected, $property, $actual = NULL) {
    if ($property !== NULL) 
      $actual = $this->getActual($property);
    
    return array($expected, $actual);
  }
  
  protected function assertNotBigObjects($expected, $actual, $property = NULL) {
    if ($this->isBigObject($expected) || $this->isBigObject($actual)) {
      throw new \Psc\Exception('Objektvergleich mit zu großen Objekten nicht möglich. (Property: '.$property.') equalsCollection oder equalsEntity bitte benutzen!');
    }
  }
  
  /**
   *
   * @param string $assertType array|bool|float|int|null|numeric|object|resource|string oder className
   * @return mixed
   */
  protected function getActual($property, $assertType = NULL) {
    $getter = \Psc\Code\Code::castGetter($property);
    
    $actual = $getter($this->entity);
    
    if (func_num_args() === 2) {
      $this->test->assertInstanceOf($assertType, $actual, 'assertType für Property: '.$property);
    }
    
    return $actual;
  }
}
?>