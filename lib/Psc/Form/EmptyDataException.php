<?php

namespace Psc\Form;

/**
 * Wird diese Exception in einer Rule geworfen und ist der validate() mit optional aufgerufen worden, wird keine validatorexception geworfen
 */
class EmptyDataException extends \Psc\Exception {
  
  
  public static function factory($defaultValue, \Exception $previous = NULL) {
    $e = new EmptyDataException(NULL, 0, $previous);
    $e->setDefaultValue($defaultValue);
    return $e;
  }
  
  /**
   * Die DefaultValue für die Rückgabe wenn die Rule OPTIONAL markiert ist und die eingabe leer ist
   *
   */
  protected $defaultValue = NULL;
  
  /**
   * @param mixed $defaultValue
   * @chainable
   */
  public function setDefaultValue($defaultValue) {
    $this->defaultValue = $defaultValue;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDefaultValue() {
    return $this->defaultValue;
  }
}
?>