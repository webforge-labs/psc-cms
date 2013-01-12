<?php

namespace Psc\Data\Type;

class StringType extends \Psc\Data\Type\Type implements \Psc\Doctrine\ExportableType, \Psc\Data\Type\MappedComponentType, \Psc\Data\Type\ValidationType, WalkableHintType {

  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('TextField');
  }
  
  public function getWalkableHint() {
    return 'String';
  }
  
  public function getDoctrineExportType() {
    return 'string';
  }
  
  public function getValidatorRule(TypeRuleMapper $mapper) {
    return $mapper->createRule('Nes');
  }
}
?>