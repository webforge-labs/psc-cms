<?php

namespace Psc\Data\Type;

class BirthdayType extends \Psc\Data\Type\DateType implements MappedComponentType {

  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('BirthdayPicker');
  }
}
?>