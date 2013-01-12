<?php

namespace Psc\Doctrine;

use Psc\Doctrine\Helper AS DoctrineHelper; // nur der Gewohnheit wegen

/**
 *
 * @todo gucken ob diese Klasse noch benutzt wird, wenn ja zu Psc\Data\Set refactorn
 */
class EntityDataRow extends \Psc\Object {
  
  const TYPE_COLLECTION = 'collection';
  
  /**
   * @var array Schlüssel sind Namen Werte sind Spezifikationen für die Daten
   */
  protected $meta;
  
  /**
   * @var array Schlüssel sind Namen der Einträge in meta
   */
  protected $data;
    
  /**
   * Die Klasse des Entities für den diese DataRow ist
   *
   */
  protected $entityName;
  
  public function __construct($name, Array $data = array()) {
    $this->entityName = DoctrineHelper::getEntityName($name);
    $this->meta = array();
    $this->data = array();
    $this->addData($data);
  }
  
  
  /**
   * @chainable
   */
  public function add($propertyName, $data, $meta = NULL) {
    $this->meta[$propertyName] = $meta;
    $this->data[$propertyName] = $data;
    return $this;
  }
  
  /**
   * @chainable
   */
  public function remove($propertyName) {
    if ($this->has($propertyName)) {
      unset($this->meta[$propertyName]);
      unset($this->data[$propertyName]);
    }
    return $this;
  }
  
  public function getData($propertyName = NULL) {
    if ($propertyName === NULL) {
      return (object) $this->data;
    } elseif($this->has($propertyName)) {
      return $this->data[$propertyName];
    }
    return NULL;
  }
  
  public function setData($propertyName, $value, $meta = NULL) {
    return $this->add($propertyName, $value, $meta);
  }

  public function getMeta($propertyName) {
    return $this->meta[$propertyName];
  }
  
  public function setMeta($propertyName, $meta) {
    if (!array_key_exists($propertyName, $this->data)) {
      throw new Exception('Es muss erst addData für das Property gemacht werden bevor meta gesetzt werden kann. Property: '.$propertyName);
    }
    $this->meta[$propertyName] = $meta;
    return $this;
  }
  
  /**
   * Gibt eine Liste der Properties der Row zurück
   * 
   * @return string[]
   */
  public function getProperties() {
    return array_keys($this->meta);
  }
  
  /**
   * @return bool
   */
  public function has($propertyName) {
    return array_key_exists($propertyName, $this->meta);
  }
  
  public function addData($dataCollection) {
    foreach ($dataCollection as $propertyName => $data) {
      $this->add($propertyName, $data, NULL);
    }
  }  
}
?>