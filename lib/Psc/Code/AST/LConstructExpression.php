<?php

namespace Psc\Code\AST;

/**
 * 
 */
class LConstructExpression extends LExpression {
  
  /**
   * @var Psc\Code\AST\LClassName
   */
  protected $class;
  
  /**
   * @var Psc\Code\AST\LArguments
   */
  protected $arguments;
  
  public function __construct(LClassName $class, LArguments $arguments) {
    $this->setClass($class);
    $this->setArguments($arguments);
  }
  
  /**
   * @deprecated
   */
  public function getClass() {
    return $this->class;
  }
  
  /**
   * @param Psc\Code\AST\LClassName $class
   */
  public function setClass(LClassName $class) {
    $this->class = $class;
    return $this;
  }
  
  /**
   * @param Psc\Code\AST\LArguments $arguments
   */
  public function setArguments(LArguments $arguments) {
    $this->arguments = $arguments;
    return $this;
  }
  
  /**
   * @return Psc\Code\AST\LArguments
   */
  public function getArguments() {
    return $this->arguments;
  }
}
?>