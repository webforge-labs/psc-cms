<?php

namespace Psc\Code\AST;

class LExpression extends Element {
  
  /**
   * @var mixed
   */
  protected $content;
  
  public function __construct($content) {
    $this->content = $content;
  }
  
  public function unwrap() {
    return $this->content;
  }
}
?>