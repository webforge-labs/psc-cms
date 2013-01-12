<?php

namespace Psc\Graph;

class Vertice extends \Psc\Object {
  
  public $label;

  public function __construct($label) {
    $this->label = $label;
  }
  
  public function __toString() {
    return $this->label;
  }
  
  public function getLabel() {
    return $this->label;
  }
}

?>