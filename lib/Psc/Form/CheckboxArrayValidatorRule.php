<?php

namespace Psc\Form;

/**
 * Gibt den Array von den Namen der Checkboxen zurück, die angecheckt wurden
 *
 * im formular muss dann checkboxname[] gemacht werden
 */
class CheckboxArrayValidatorRule implements ValidatorRule {
  
  public function validate($data) {
    if ($data === NULL || $data === array()) {
      throw EmptyDataException::factory(array());
    }

    $data = (array) $data;
    
    if (count($data) == 0) {
      throw EmptyDataException::factory(array());
    }
    
    return $data;
  }
}

?>