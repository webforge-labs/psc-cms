<?php

namespace Psc\PHPExcel;

class Sheet {
  
  /**
   * @var array[]
   */
  protected $rows;
  
  /**
   * @var string
   */
  protected $name;
  
  public function __construct($name, Array $rows) {
    $this->name = $name;
    $this->rows = $rows;
  }
  
  public function getName() {
    return $this->name;
  }
  
  public function getRows() {
    return $this->rows;
  }
  
  public function __toString() {
    return sprintf("[Psc\PHPExcel\Sheet: '%s']", $this->name);
  }
}
?>