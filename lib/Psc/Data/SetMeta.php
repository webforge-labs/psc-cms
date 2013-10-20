<?php

namespace Psc\Data;

use Psc\DataInput;
use Psc\Code\Code;
use Webforge\Common\ArrayUtil AS A;
use Webforge\Types\Type;
use Webforge\Types\TypeExpectedException;

class SetMeta extends \Psc\SimpleObject {
  
  /**
   * @var Webforge\Types[] Schlüssel sind Namen der Felder, Werte sind Webforge\Types\Type-Objekte
   */
  protected $types;
  
  /**
   * @param array Webforge\Types[] Schlüssel sind Namen der Felder (Ebenen getrennt mit .), Werte sind Webforge\Types\Type-Objekte
   */
  public function __construct(Array $types = array()) {
    $this->types = new DataInput();
    $this->setTypesFromArray($types);
  }
  
  /**
   * Gibt den Typ eines Feldes zurück
   * 
   * @param string|array wenn string dann ebenen mit . getrennt
   * @return Webforge\Types
   * @throws FieldNotDefinedException
   */
  public function getFieldType($field) {
    try {
      $key = $this->getKey($field);
      
      // dies kann auch einen verschachtelten Array von Types zurück geben, wenn der Schlüssel den man angibt noch Untertypen hat
      return $this->types->get($key, DataInput::THROW_EXCEPTION, DataInput::THROW_EXCEPTION);
    
    } catch (\Psc\DataInputException $e) {
      $e = new FieldNotDefinedException(sprintf("Feld mit den Schluessel(n) '%s' ist nicht in Meta definiert. Vorhanden sind (Ebenen durch . getrennt): %s",
                                                $key, implode(', ', $avFields = $this->getKeys())),
                                        0, $e);
      $e->field = explode(':',$key); // expand
      $e->avaibleFields = $avFields;
      throw $e;
    }
  }
  
  /**
   * @return bool
   */
  public function hasField($field) {
    return $this->types->get($this->getKey($field), FALSE, FALSE) !== FALSE;
  }
  
  /**
   * Setzt den Typ für ein Feld
   *
   * wenn das Feld nicht existiert, wird es angelegt
   * @param string|array wenn string dann ebenen mit . getrennt
   */
  public function setFieldType($field, Type $type) {
    $this->types->set($this->getKey($field), $type);
    return $this;
  }

  /**
   * @param array Webforge\Types[] Schlüssel sind Namen der Felder (Ebenen getrennt mit .), Werte sind Webforge\Types\Type-Objekte
   */
  public function setTypesFromArray(Array $types) {
    foreach ($types as $field => $type) {
      if (!($type instanceof Type)) {
        throw new TypeExpectedException('Instances from Type as values from the array expected. '.Code::varInfo($type).' was provided.');
      }
      
      $this->setFieldType($field, $type);
    }
    return $this;
  }

  /**
   * @param array Webforge\Types[] Schlüssel sind Namen der Felder (Ebenen getrennt mit .), Werte sind Webforge\Types\Type-Objekte
   */
  public function addTypesFromArray(Array $types) {
    foreach ($types as $field => $type) {
      if (!($type instanceof Type)) {
        throw new TypeExpectedException('Instances from Type as values from the array expected. '.Code::varInfo($type).' was provided.');
      }
      
      $this->setFieldType($field, $type);
    }
    return $this;
  }
  
  /**
   * Gibt alle Typen zurück
   *
   * @return array die Schlüssel des Arrays sind mit . getrennt (und umspannen alle Ebenen)
   */
  public function getTypes() {
    $typesExport = array();
    foreach ($this->types->toArray() as $key => $type) {
      $typesExport[str_replace(':','.',$key)] = $type;
    }
    return $typesExport;
  }
  
  /**
   * Gibt alle Schlüssel als flat Array zurück
   *
   * d.h. alle Schlüssel werden als strings getrennt mit . zurückgegeben (auch die, der unterliegenden ebenen)
   * die keys sind also alle pfade zu den typen in diesem meta
   */
  public function getKeys() {
    return array_keys($this->getTypes());
  }
  
  /**
   * @return Psc\Data\SetMeta
   */
  public static function factory(Array $types = array()) {
    return new static($types);
  }
  
  /**
   * @return mixed
   */
  protected function getKey($field) {
    // siehe auch expand bei der exception und getTypes() (falls hier was geändert wird)
    if (is_array($field)) {
      $key = implode(':',$field); // flatten, nicht . nehmen denn das nimmt Psc\data\Input auseinander
    } else {
      $key = str_replace('.',':',$field); // flatten, nicht . nehmen denn das nimmt Psc\data\Input auseinander
    }
    return $key;
  }
}
