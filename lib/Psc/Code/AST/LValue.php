<?php

namespace Psc\Code\AST;

/**
 * Eine Terminal der Programmiersprache
 */
class LValue extends Element {
  
  /**
   * @var mixed
   */
  protected $value;
  
  /**
   * Der Typ der beim Parsen der Value geraten wurde (muss nicht  zwingend gesetzt sein)
   * @var Psc\Code\AST\LType
   */
  protected $inferredType;
  
  public function __construct($value, LType $inferredType = NULL) {
    $this->setValue($value);
    if (isset($inferredType))
      $this->setInferredType($inferredType);
  }
  
  /**
   * @param mixed $value
   */
  public function setValue($value) {
    $this->value = $value;
    return $this;
  }
  
  /**
   * @return mixed
   */
  public function getValue() {
    return $this->value;
  }
  
  /**
   * @return mixed
   */
  public function unwrap() {
    return $this->getValue();
  }
  
  /**
   * @param Psc\Code\AST\Type $inferredType
   */
  protected function setInferredType(LType $inferredType) {
    $this->inferredType = $inferredType;
    return $this;
  }
  
  /**
   * @return Psc\Code\AST\Type
   */
  public function getInferredType() {
    return $this->inferredType;
  }
}
?>