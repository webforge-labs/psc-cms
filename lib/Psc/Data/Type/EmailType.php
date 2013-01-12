<?php

namespace Psc\Data\Type;

class EmailType extends \Psc\Data\Type\StringType implements MappedComponentType {
  
  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('EmailField');
  }
}
?>