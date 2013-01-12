<?php

namespace Psc\Data\Type;

use Psc\Form\PositiveIntValidatorRule;

class PositiveIntegerType extends \Psc\Data\Type\IntegerType implements ValidationType, MappedComponentType {
  
  public function getValidatorRule(TypeRuleMapper $mapper) {
    return $mapper->createRule(new PositiveIntValidatorRule($this->hasZero()));
  }
  
  
  /**
   *
   * @return Psc\CMS\Component
   */
  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('IntegerField');
  }
}
?>