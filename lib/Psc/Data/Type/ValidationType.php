<?php

namespace Psc\Data\Type;

interface ValidationType {
  
  /**
   *
   * $mapper->createRule('Id');
   * => Psc\Form\IdValidatorRule
   *
     public function getValidatorRule(TypeRuleMapper $mapper) {
       return $mapper->createRule('Id');
     }
   *
   * @return Psc\Form\ValidatorRule
   */
  public function getValidatorRule(TypeRuleMapper $mapper);

}
?>