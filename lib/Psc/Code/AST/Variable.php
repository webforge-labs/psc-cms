<?php

namespace Psc\Code\AST;

// legacy klasse die ein paar mal schon vorher benutzt wurde
class Variable extends LVariable {
  
  public function __construct($name, LType $type = NULL) {
    parent::__construct($name, $type ?: new LType('Mixed'));
  }
}
?>