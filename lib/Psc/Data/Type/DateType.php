<?php

namespace Psc\Data\Type;

/**
 *
 * wir leiten doch nicht von DateTimeType ab
 * Änderungen: siehe auch class DateTimeType
 */
class DateType extends \Psc\Data\Type\ObjectType implements \Psc\Doctrine\ExportableType, MappedComponentType {
  
  public function getDoctrineExportType() {
    return 'PscDate';
  }

  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('DatePicker');
  }
  
  public function __construct() {
    parent::__construct(new \Psc\Code\Generate\GClass('Psc\DateTime\Date'));
  }
}
?>