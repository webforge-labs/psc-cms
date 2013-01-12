<?php

namespace Psc\UI\Component;

class PasswordField extends \Psc\UI\Component\TextField {
  
  public function getInnerHTML() {
    $textField = parent::getInnerHTML();
    $textField->setAttribute('type','password');
    return $textField;
  }
}
?>