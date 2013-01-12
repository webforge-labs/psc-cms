<?php

namespace Psc\System\Console;

class NormalClass {
  
  /**
   * @var string
   */
  protected $name;
  
  public function __construct($name) {
    $this->name = $name;
  }
  
  /**
   * @param string $name
   * @chainable
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }
}
?>