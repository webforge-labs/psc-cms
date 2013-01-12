<?php

namespace Psc\Data\Type;

class IdType extends \Psc\Data\Type\PositiveIntegerType implements ValidationType, MappedComponentType {
  
  public function __construct() {
    parent::__construct();
    $this->setZero(FALSE);
  }
  
  public function getValidatorRule(TypeRuleMapper $mapper) {
    return $mapper->createRule('Id');
  }
  
  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('IntegerField');
  }
}
?>