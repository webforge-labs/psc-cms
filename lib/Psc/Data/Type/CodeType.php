<?php

namespace Psc\Data\Type;

class CodeType extends TextType implements \Psc\Data\Type\MappedComponentType {

  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('CodeEditor');
  }
}
?>