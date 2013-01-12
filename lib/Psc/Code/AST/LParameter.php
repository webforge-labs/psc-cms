<?php

namespace Psc\Code\AST;

/**
 * Ein Parameter einer Funktion (oder Methode)
 */
class LParameter extends Element {
  
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var Psc\Code\AST\LType
   */
  protected $type;
  
  public function __construct($name, LType $type) {
    $this->setName($name);
    $this->setType($type);
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
   * @param Psc\Code\AST\LType $type
   */
  public function setType(LType $type) {
    $this->type = $type;
    return $this;
  }
  
  /**
   * @return Psc\Code\AST\LType
   */
  public function getType() {
    return $this->type;
  }
}
?>