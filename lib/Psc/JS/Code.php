<?php

namespace Psc\JS;

class Code implements Expression {
  
  protected $js;
  
  public function __construct($jsCode) {
    $this->js = $jsCode;
  }
  
  public function JS() {
    return $this->js;
  }
  
  public static function fromCode(Array $codeLines) {
    return new static(\Psc\A::join($codeLines, "%s\n"));
  }
  
  public function __toString() {
    return '[Psc\JS\Code]: '.$this->JS();
  }
}
?>