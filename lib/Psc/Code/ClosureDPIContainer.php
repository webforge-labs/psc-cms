<?php

namespace Psc\Code;

class ClosureDPIContainer extends DPIContainer {
  
  public function __construct() {
    parent::__construct();
    $callable = $this->constructors['object1']; 
    
    $this->values['object1'] = function () use ($callable) {
      static $object;
      
      if (null === $object) {
        $object = $callable();
      }

      return $object;
    };
  }
  
  public function getObject() {
    return $this->values['object1']();
  }
}
?>