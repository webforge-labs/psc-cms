<?php

namespace Psc\Data\Type;

use Psc\Code\Generate\GClass;

/**
 *
 * Änderungen: siehe auch class DateType
 */
class DateTimeType extends \Psc\Data\Type\ObjectType implements \Psc\Doctrine\ExportableType, MappedComponentType, ValidationType {
  
  public function getDoctrineExportType() {
    return 'PscDateTime';
    //return \Doctrine\DBAL\Types\Type::DATETIME;
  }
  
  public function getValidatorRule(TypeRuleMapper $mapper) {
    return $mapper->createRule('DateTime');
  }

  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('DateTimePicker');
  }
  
  public function __construct() {
    parent::__construct(new GClass('Psc\DateTime\DateTime'));
  }
  
}
?>