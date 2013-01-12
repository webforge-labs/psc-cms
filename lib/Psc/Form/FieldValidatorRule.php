<?php

namespace Psc\Form;

/**
 * Die FieldValidatorRule kann anstatt der ValidatorRule benutzt werden, wenn man zusätzlich noch den Feldnamen benötigt
 *
 * setField wird dann -vor- validate() aufgerufen
 */
interface FieldValidatorRule extends ValidatorRule {
  
  /**
   * @param string $field
   */
  public function setField($field);
  
}