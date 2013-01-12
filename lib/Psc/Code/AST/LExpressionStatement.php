<?php

namespace Psc\Code\AST;

class LExpressionStatement extends LStatement {
  
  protected $expression;
  
  public function __construct(LExpression $expression) {
    $this->expression = $expression;
  }
  
  public function getExpression() {
    return $this->expression;
  }
}
?>