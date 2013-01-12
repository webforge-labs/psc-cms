<?php

namespace Psc\Code;

abstract class DPIContainer {
  
  protected $values = array();
  protected $constructors = array();

  public function __construct() {
    $this->constructors['object1'] = function () {
      return new MyInjectedObject();
    };
  }
  
  abstract public function getObject();
}
?>