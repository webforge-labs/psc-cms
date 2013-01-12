<?php

namespace Psc\Doctrine;

class EntityDataNotEqualException extends Exception {
  
  public $entity;
  public $property;
  public $expected;
  public $actual;
  
  public function __construct($entity, $property, $expected, $actual) {
    $this->entity = $entity;
    $this->property = $property;
    $this->expected = $expected;
    $this->actual = $actual;
    parent::__construct(sprintf("Daten für property: '%s' sind nicht, wie erwartet, gleich.",
                                $property));
  }
}
?>