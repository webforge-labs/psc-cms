<?php

namespace Psc\Data;

use Psc\DataInput;
use Psc\Code\Code;

class Set extends \Psc\SimpleObject implements \Psc\Form\ValidatorDataProvider, Walkable, \IteratorAggregate {
  
  /**
   * @var Psc\DataInput
   */
  protected $fields;
  
  /**
   * @var SetMeta
   */
  protected $meta;
  
  /**
   * Erstellt ein neues Datenset
   *
   * 
   * die übergebenen Felder müssen auch in $meta existieren
   * es ist einfacher createFromStruct zu benutzen, wenn man die feld-Schlüssel nicht 2 mal tippen will
   */
  public function __construct(Array $fields = array(), SetMeta $meta = NULL) {
    $this->fields = new DataInput();
    $this->meta = $meta ?: new SetMeta();
    
    $this->setFieldsFromArray($fields);
  }
  
  /**
   * Gibt die Daten für das Feld zurück
   *
   * ist das Feld in Meta definiert, aber keine Daten gesetzt worden wird NULL zurückgegeben
   * @param array|string wenn string dann ebenen mit . getrennt
   * @return mixed|NULL
   * @throws FieldNotDefinedException
   */
  public function get($field) {
    // wir machen hier erstmal nicht so performant immer einen meta-check
    $this->meta->getFieldType($field); // throws FieldNotDefinedException
      
    return $this->fields->get($field, NULL, NULL); // returned immer NULL wenn nicht gesetzt
  }
  
  /**
   *
   * der Type muss für das Feld angegeben sein oder vorher gesetzt worden sein
   * Wird der Type mit übergeben wird IMMER der Type von vorher in meta überschrieben
   * @param array|string wenn string dann ebenen mit . getrennt
   * @throws FieldNotDefinedException
   */
  public function set($field, $value, Type\Type $type = NULL) {
    if (isset($type)) {
      $this->meta->setFieldType($field, $type);
    } else {
      // wir machen hier erstmal nicht so performant immer einen meta-check
      $type = $this->meta->getFieldType($field); // throws FieldNotDefinedException
    }
    
    $this->fields->set($field, $value);
    return $this;
  }
  
  
  /**
   * @return array nur die Namen der Schlüssel (Ebenen mit . getrennt)
   */
  public function getKeys() {
    return $this->meta->getKeys();
  }
  
  /**
   * Gibt nur die Schlüssel der Ebene 0 zurück
   *
   * @return array
   */
  public function getRootKeys() {
    return array_keys($this->fields->toArray());
  }
  
  /**
   * Gibt alle Felder der Ebene 0 zurück
   *
   * (standard foreach)
   * darunter können dann weitere Schlüssel verschachtelt sein
   * @return array
   */
  public function getIterator() {
    return new \ArrayIterator($this->fields->toArray());
  }
  
  /**
   *
   * der gleiche Array wie bei getIterator() (somit bei foreach auch )
   * @return array
   */
  public function toArray() {
    return $this->fields->toArray();
  }
  
  /**
   * Setzt mehrere Felder aus einem Array
   *
   * die Felder die als Schlüssel angegeben werden, müssen alle als Meta existieren
   * @param array $fields Schlüssel sind mit . getrennte strings Werte sind die Werte der Felder
   */
  public function setFieldsFromArray(Array $fields) {
    foreach ($fields as $field => $value) {
      $this->set($field, $value);
    }
    return $this;
  }
  
  /* INTERFACE: ValidatorDataProvider */
  public function getValidatorData($keys) {
    return $this->get($keys);
  }
  /* END INTERFACE: ValidatorDataProvider */
  
  /**
   * @param Psc\Data\SetMeta $meta
   * @chainable
   */
  public function setMeta(SetMeta $meta) {
    $this->meta = $meta;
    return $this;
  }

  /**
   * @return Psc\Data\SetMeta
   */
  public function getMeta() {
    return $this->meta;
  }
  
  /* INTERFACE: Walkable */
  public function getWalkableFields() {
    $fields = array();
    // hier so umständlich, weil meta->getTypes() uns die fields "geflattet" zurückgibt (und das wollen wir)
    foreach ($this->meta->getTypes() as $field => $type) {
      $fields[$field] = $this->get($field);
    }
    return $fields;
  }
  
  public function getWalkableType($field) {
    return $this->meta->getFieldType($field);
  }
  
  /* END INTERFACE: Walkable */
  
  /**
   * Erstellt ein Set mit angegebenen Metadaten
   *
   * Shortcoming um die Felder des Sets nicht doppelt bezeichnen zu müssen
   * struct ist ein Array von Listen mit jeweils genau 2 elementen (key 0 und key 1)
   * 
   * @param list[] $struct die Listen sind von der Form: list(mixed $fieldValue, Psc\Data\Type\Type $fieldType). Die Schlüssel sind die Feldnamen
   * @return Set
   */
  public static function createFromStruct(Array $struct, SetMeta $meta = NULL) {
    if (!isset($meta)) $meta = new SetMeta();
    $set = new static(array(), $meta);
    
    foreach ($struct as $field => $list) {
      list($value, $type) = $list;
      $set->set($field, $value, $type);
    }
    
    return $set;
  }
  
  public function setFieldType($field, $type) {
    $this->meta->setFieldType($field, $type);
    return $this;
  }

  public function getFieldType($field) {
    return $this->meta->getFieldType($field);
  }
  
  /**
   * @params array
   */
  public static function create() {
    $args = func_get_args();
    if (count($args) === 1 && is_array($args[0])) {
      return static::createFromStruct($args[0]);
    }
    
    $signatur = array();
    foreach ($args as $arg) {
      $signatur[] = Code::getType($arg);
    }

    throw new \BadMethodCallException('Es wurde keine Create-Methode für die Parameter: '.implode(', ',$signatur).' gefunden');
  }
}
?>