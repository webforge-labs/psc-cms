<?php

namespace Psc\Code;

class IssetDPIContainer extends DPIContainer {
  
  public function __construct() {
    parent::__construct();
  }
  
  public function getObject() {
    if (!isset($this->values['object1'])) {
      $this->values['object1'] = $this->constructors['object1']();
    }
    
    return $this->values['object1'];
  }
}
?>