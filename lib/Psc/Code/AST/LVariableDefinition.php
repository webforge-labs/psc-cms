<?php

namespace Psc\Code\AST;

/**
 * Definiert die Variable $this->variable mit dem wert $this->value
 *
 * $value ist optional
 */
class LVariableDefinition extends LStatement {
  
  /**
   * @var Psc\Code\AST\LVariable
   */
  protected $variable;
  
  /**
   * Die Initialisierungs Value oder Expression
   * 
   * @var Psc\Code\AST\LValue|LExpression
   */
  protected $initializer;
  
  public function __construct(LVariable $variable, $initializer = NULL) {
    $this->setVariable($variable);
    $this->initializer = $initializer;
  }

  /**
   * @return bool
   */
  public function hasValue() {
    return $this->initializer instanceof LValue;
  }
  
  /**
   * @param Psc\Code\AST\LVariable $variable
   */
  public function setVariable(LVariable $variable) {
    $this->variable = $variable;
    return $this;
  }
  
  /**
   * @return Psc\Code\AST\LVariable
   */
  public function getVariable() {
    return $this->variable;
  }
  
  /**
   * @return Psc\Code\AST\LValue|Psc\Code\AST\LExpression
   */
  public function getInitializer() {
    return $this->initializer;
  }
}
?>