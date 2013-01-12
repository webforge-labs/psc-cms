<?php

namespace Psc\Code\AST;

/**
 * 
 */
class LVariable extends Element {
  
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var Psc\Code\AST\LType
   */
  protected $type;
  
  public function __construct($name, LType $type) {
    $this->name = $name;
    $this->setType($type);
  }
  
  /**
   * @param string $name
   * @param string|Ltype $type
   * @return LVariable
   */
  public static function create($name, $type) {
    if (!($type instanceof LType)) {
      $type = new LType($type);
    }
    
    return new static($name, $type);
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