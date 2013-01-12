<?php

namespace Psc\Form;

class PasswordConfirmationException extends PasswordRuleException {
  
  public function __construct() {
    parent::__construct('Die angegebenen Passwörter stimmen nicht überein.');
  }
}
?>