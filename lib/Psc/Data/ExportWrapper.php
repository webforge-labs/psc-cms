<?php

namespace Psc\Data;

class ExportWrapper extends \Psc\SimpleObject implements Exportable {
  
  protected $inner;
  
  public function __construct($inner) {
    // make a copy
    if (is_object($inner)) {
      $this->inner = clone $inner;
    } else {
      $this->inner = $inner;
    }
  }
  
  public function export() {
    $value = $this->inner;
    
    return $value;
  }
  
  public function unwrap() {
    return $this->innner;
  }
}
?>