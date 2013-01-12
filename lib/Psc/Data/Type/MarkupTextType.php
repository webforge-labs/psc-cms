<?php

namespace Psc\Data\Type;

class MarkupTextType extends \Psc\Data\Type\TextType {

  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('TextBox');
  }
}
?>