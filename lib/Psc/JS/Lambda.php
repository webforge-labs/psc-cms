<?php

namespace Psc\JS;

class Lambda implements Expression {
  
  protected $js;
  
  public function __construct($jsCode) {
    $this->js = $jsCode;
  }
  
  public function JS() {
    return $this->js;
  }
}