<?php

namespace Psc\System\Console;

/**
 * 
 */
class CompiledNormalClass {
  
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var integer
   */
  protected $identifier;
  
  public function __construct($name, $identifier) {
    $this->name = $name;
    $this->setIdentifier($identifier);
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
  
  /**
   * @param integer $identifier
   */
  public function setIdentifier($identifier) {
    $this->identifier = $identifier;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getIdentifier() {
    return $this->identifier;
  }
}
?>