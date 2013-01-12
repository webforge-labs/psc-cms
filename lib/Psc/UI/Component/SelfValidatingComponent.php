<?php

namespace Psc\UI\Component;

interface SelfValidatingComponent {
  
  /**
   * If this interface is implemented this method should return the validated value
   *
   * onValidation() is called anyways
   * @return the validated value
   */
  public function validate(\Psc\Form\ComponentsValidator $validator);
  
}
?>