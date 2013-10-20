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
   * @var Psc\Data\Type\Type
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
   * @param Psc\Data\Type\Type $type
   */
  public function setType(Type $type) {
    $this->type = $type;
    return $this;
  }
  
  /**
   * @return Psc\Data\Type\Type
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
