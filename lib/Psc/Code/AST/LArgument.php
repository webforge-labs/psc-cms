<?php

namespace Psc\Code\AST;

/**
 * 
 */
class LArgument extends Element {
  
  /**
   * Der "Wert" der dem Argument zurgeordnet wird
   *
   * dies kann eine BaseValue sein, eine Expression (etwas, was ein Terminal zurückgibt sozusagen, also ein Funktionsaufruf oder ein Objekt etc), oder sonstiges
   * @var Psc\Code\AST\LValue|LExpression
   */
  protected $binding;
  
  /**
   * KISS: erstmal ist binding nur LValue
   */
  public function __construct($binding) {
    $this->setBinding($binding);
  }
  
  /**
   * @param Psc\Code\AST\LValue|LExpression $binding
   */
  public function setBinding($binding) {
    $this->binding = $binding;
    return $this;
  }
  
  /**
   * @return Psc\Code\AST\LValue|LExpression
   */
  public function getBinding() {
    return $this->binding;
  }
}
?>