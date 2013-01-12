<?php

namespace Psc\Data\Type;

class PositiveSmallIntegerType extends \Psc\Data\Type\PositiveIntegerType implements MappedComponentType, \Psc\Doctrine\ExportableType {
  
  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('SmallIntegerField');
  }
  
  public function getDoctrineExportType() {
    return 'smallint';
  }
}
?>