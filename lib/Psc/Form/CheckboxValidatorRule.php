<?php

namespace Psc\Form;
/**
 * Da wir davon ausgehen, dass die Data nicht definiert ist, wenn es sich um einen Checkbox handelt im Formular, muss $data nur ein string sein, damit
 * keine Exception geschmissen wird
 *
 * wenn $data jedoch kein String ist, gibt es eine validation
 */
class CheckboxValidatorRule implements ValidatorRule {
  
  public function validate($data) {
    
    if (is_string($data) || $data === NULL) {
      return ($data == 'true');
    }
    
    throw new \Psc\Exception('Konnte nicht validiert werden, da der Parameter der Checkbox kein String ist');
  }
}

?>