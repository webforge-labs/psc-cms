<?php

namespace Psc\UI\Component;

abstract class ValuesBase extends \Psc\UI\Component\Base {
  
  protected $values;
  
  /**
   * @param Array $values
   * @chainable
   */
  public function setValues(Array $values) {
    $this->values = $values;
    return $this;
  }

  /**
   * @return Array
   */
  public function getValues() {
    return $this->values;
  }
}
?>