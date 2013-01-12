<?php

namespace Psc\System\Console;

require_once __DIR__.DIRECTORY_SEPARATOR.'class.PropertyAddBaseTestClass.php';

class PropertyAddTestClass extends PropertyAddBaseTestClass {
  
  /**
   * @var Traversable
   */
  protected $assignedValues;
  
  public function __construct($valuesCollection, $flags = 0) {
    $this->assignedValues = $valuesCollection; 
  }
  
  protected function doInit() {
  }
  
  public function toBeImplemented() {
    
  }
}
?>