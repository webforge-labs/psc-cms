<?php

namespace Psc\Data\Type;

class SmallIntegerType extends \Psc\Data\Type\IntegerType implements MappedComponentType, \Psc\Doctrine\ExportableType {
  
  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('SmallIntegerField');
  }
  
  public function getDoctrineExportType() {
    return 'smallint';
  }
}
?>