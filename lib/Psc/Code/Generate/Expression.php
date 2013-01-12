<?php

namespace Psc\Code\Generate;

class Expression extends \Psc\SimpleObject implements \Psc\Data\Exportable, \Psc\Code\PHPInterface {
  
  protected $php;
  
  public function __construct($phpCode) {
    if (!is_string($phpCode))
      throw new \InvalidArgumentException('Parameter $phpCode muss unbedingt ein String sein!');
    $this->php = $phpCode;
  }
  
  public function export() {
    return $this->php;
  }
  
  public function __toString() {
    return $this->php;
  }

  public function php() {
    return $this->php;
  }
}
?>