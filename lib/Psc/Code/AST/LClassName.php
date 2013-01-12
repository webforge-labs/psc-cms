<?php

namespace Psc\Code\AST;

/**
 * erstmal LValue weil ich nicht weiß, was ich davon brauche. Wir stellen uns den ClassName einfach als String vor
 */
class LClassName {
  
  /**
   * @var Psc\Code\AST\LValue
   */
  protected $value;
  
  public function __construct($className) {
    $this->setValue(new LValue($className, new LType('String')));
  }
  
  public function toString() {
    return $this->value->unwrap();
  }
  
  /**
   * @param Psc\Code\AST\LValue $value
   */
  public function setValue(LValue $value) {
    $this->value = $value;
    return $this;
  }
  
  /**
   * @return Psc\Code\AST\LValue
   */
  public function getValue() {
    return $this->value;
  }
}
?>