<?php

namespace Psc\Data;

use Webforge\Types\Type;

/**
 * 
 */
class Column extends \Psc\SimpleObject {
  
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var Webforge\Types\Type
   */
  protected $type;
  
  /**
   * @var string
   */
  protected $label;
  
  public function __construct($name, Type $type, $label) {
    $this->setName($name);
    $this->setType($type);
    $this->setLabel($label);
  }
  
  /**
   * @param string $name
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
   * @param Webforge\Types\Type $type
   */
  public function setType(Type $type) {
    $this->type = $type;
    return $this;
  }
  
  /**
   * @return Webforge\Types\Type
   */
  public function getType() {
    return $this->type;
  }
  
  /**
   * @param string $label
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }
}
