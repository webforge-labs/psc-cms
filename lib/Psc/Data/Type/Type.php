<?php

namespace Psc\Data\Type;

use Psc\Object;
use Psc\Code\Code;
use Psc\Code\Generate\GClass;
use Psc\Preg;

abstract class Type extends \Psc\SimpleObject {
  
  const CONTEXT_DEFAULT = 'context_default';
  const CONTEXT_DOCBLOCK = 'context_docblock';
  const CONTEXT_DEBUG = 'context_debug';

  public function __construct() {
  }
  
  public function getName($context = self::CONTEXT_DEFAULT) {
    if ($context === self::CONTEXT_DOCBLOCK)
      return $this->getDocType();
    
    // damit wir in den unterobjekten getClass und getClassName() benutzen können benutzen wir hier nicht die SimpleObject - Funktionen
    return mb_substr(Code::getClassName(Code::getClass($this)),0,-4); //-mb_strlen('Type')
  }
  
  /**
   * @return GClass
   */
  public function getTypeClass() {
    return new GClass(Code::getClass($this));
  }
  
  /**
   * Gibt eine Instanz für einen DatenTyp zurück
   *
   * Der Name darf nur für nicht abstrakte Type-Klassen weggelassen werden
   *
   * Es ist möglich folgende Shorthands zu benutzen:
   *   TypeName[] => ein Array mit dem inneren Typ mit dem namen "TypeName"
   *   Object<Class\FQN> => ein Objekt mit dem inneren Type der Klasse "Class\FQN"
   *
   * @param $name definition des Types
   */
  public static function create($name = NULL) {
    $c = self::expandName($name);
    
    if (is_string($c)) {
      return GClass::newClassInstance($c, array_slice(func_get_args(), 1));
    } elseif ($c instanceof Type) {
      return $c;
    } else {
      throw Exception::invalidArgument(1, __FUNCTION__, $name, self::create('String'));
    }
  }
  
  public static function createArgs($name, Array $args = array()) {
    $c = self::expandName($name);
    
    if (is_string($c)) {
      return GClass::newClassInstance($c, $args);
    } elseif ($c instanceof Type) {
      return $c;
    } else {
      throw Exception::invalidArgument(1, __FUNCTION__, $name, self::create('String'));
    }
  }
  
  protected static function expandName($name = NULL) {
    if (!isset($name)) {
      $c = get_called_class();
      if ($c === __CLASS__) {
        throw new Exception('create() ohne Parameter ist für Type selbst nicht erlaubt');
      }
    } elseif ($fqn = Preg::qmatch($name, '/^Object<(.*)>$/')) {
      return new ObjectType(new GClass($fqn));
    } elseif ($fqn = Preg::qmatch($name, '/^Collection<(.*)>$/')) {
      return new CollectionType(CollectionType::PSC_ARRAY_COLLECTION, new ObjectType(new GClass($fqn)));
    } elseif (\Psc\String::endsWith($name, '[]')) {
      return new ArrayType(self::create(mb_substr($name, 0,-2)));
    } elseif (mb_strpos($name, '\\') !== FALSE) { // eigenen klassen name
      return $name;
    } else {
      $c = sprintf('%s\%sType', __NAMESPACE__, $name);
    }
    
    return $c;
  }
  
  public static function parseFromDocBlock($type) {
    if ($type === 'stdClass') {
      return new ObjectType();
    }
    
    $type = trim($type);
    
    if (mb_strpos($type, '\\') !== FALSE && !\Psc\String::startsWith($type, 'Object<')) {
      return self::create('Object<'.$type.'>');
    }
    
    $type = ucfirst($type);
    return self::create($type);
  }
  
  /**
   * Gibt den internen PHPType des Types zurück
   *
   */
  public function getPHPType() {
    if ($this instanceof \Psc\Data\Type\StringType) {
      return 'string';
    } elseif ($this instanceof \Psc\Data\Type\IntegerType) {
      return 'integer';
    } elseif ($this instanceof \Psc\Data\Type\BooleanType) {
      return 'bool';
    } elseif ($this instanceof \Psc\Data\Type\ArrayType) {
      return 'array';
    } elseif ($this instanceof \Psc\Data\Type\InterfacedType) {
      return $this->getInterface();
    } elseif ($this instanceof \Psc\Data\Type\CompositeType) {
      throw new Exception(sprintf("CompositeType '%s' muss getPHPType() definieren", \Psc\Code\Code::getClass($this)));
    } 
    
    throw new Exception('unbekannter Fall für getPHPType() für Klasse: '.\Psc\Code\Code::getClass($this));
  }
  
  /**
   * Gibt den DocBlock-Type des Typen zurück
   *
   * wird z.b. hinter @var geschrieben und kann damit auch ein PseudoType sein
   */
  public function getDocType() {
    try {
      return $this->getPHPType();
    } catch (\Exception $e) {
      throw new Exception('Fallback zu PHPType beim ermitteln von DocType gab einen Fehler:'. $e->getMessage().' getDocType() des Types überschreiben.');
    }
  }

  /**
   * @return string bei Klassen ist das der FQN (ohne \ davor)
   */
  public function getPHPHint() {
    if ($this instanceof \Psc\Data\Type\ArrayType) {
      return 'Array';
    } elseif ($this instanceof \Psc\Data\Type\InterfacedType) {
      return $this->getInterface();
    } elseif ($this instanceof \Psc\Data\Type\CompositeType) {
      throw new Exception(sprintf("CompositeType '%s' muss getPHPHint() definieren. oder $phpHint setzen",\Psc\Code\Code::getClass($this)));
    } else {
      return NULL;
    }
  }

  /**
   * 
   * kann in getInterface vom InterfacedType benutzt werden um getInterface zu implementieren
   * sollte benutzt werden, da noch nicht klar ist, ob hier GClass oder string Sinn macht
   */
  protected function getInterfaceDefinition($name) {
    return 'Psc\Data\Type\Interfaces\\'.$name;
  }
  
  public function __toString() {
    return '[Type:'.$this->getTypeClass()->getFQN().']';
  }
}
?>