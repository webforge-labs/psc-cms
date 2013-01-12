<?php

namespace Psc\Form;

interface ValidatorRule {
  
  /**
   *
   * Achtung! nicht bool zurückgeben, stattdessen irgendeine Exception schmeissen
   *
   * Wird EmptyDataException bei validate() geworfen und ist der Validator mit optional aufgerufen worden, wird keine ValidatorException geworfen
   * @return $data
   */
  public function validate($data);
  
}