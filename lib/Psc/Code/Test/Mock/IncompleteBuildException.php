<?php

namespace Psc\Code\Test\Mock;

class IncompleteBuildException extends \Psc\Exception {
  
  public static function missingVariable($variable) {
    return parent::create('Der MockBuilder ist nicht komplett. Es muss noch "%s" gesetzt werden.', $variable);
  }
}
?>