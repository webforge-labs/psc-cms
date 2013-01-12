<?php

namespace Psc\CMS;

use \Doctrine\Common\Collections\ArrayCollection;

class ContactFormData extends \Psc\Object {
  
  protected $fields;
  
  public function __construct() {
    $this->fields = new ArrayCollection();
  }
  
  public function setField($field, $value) {
    $this->fields[$field] = $value;
    return $this;
  }
  
  public function getField($field) {
    if (!$this->fields->containsKey($field)) {
      throw new Exception('Feld: '.$field.' kommt im Formulat nicht vor');
    }
    return $this->fields->get($field);
  }
  
  public function setFields($fields) {
    foreach ($fields as $field => $value) {
      $this->setField($field, $value);
    }
    return $this;
  }
}

?>