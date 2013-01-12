<?php

namespace Psc\Form;

class PasswordMinLengthException extends PasswordRuleException {

  public function __construct($minLength) {
    parent::__construct(sprintf('Das Passwort muss mindestens %d Zeichen enthalten.', $minLength));
  }
}
?>