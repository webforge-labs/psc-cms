<?php

namespace Psc\Data\Type;

class BooleanType extends \Psc\Data\Type\Type implements \Psc\Data\Type\MappedComponentType {
  
  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('Checkbox');
  }
}
?>