<?php

namespace Psc\Data\Type;

class TextType extends \Psc\Data\Type\StringType implements \Psc\Data\Type\MappedComponentType,\Psc\Doctrine\ExportableType {
  
  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('TextBox');
  }

  public function getDoctrineExportType() {
    return 'text';
  }
}
?>