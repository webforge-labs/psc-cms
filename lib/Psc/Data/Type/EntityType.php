<?php

namespace Psc\Data\Type;

use Psc\Code\Generate\GClass;

class EntityType extends ObjectType implements MappedComponentType {
  
  /**
   *
   * am ebesten den $componentMapper zu benutzen um die Componente zu instanziieren:
   *
   *  return $componentMapper->createComponent('BirthdayPicker');
   * 
   * @return Psc\CMS\Component
   */
  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    if ($this->isImage()) {
      return $componentMapper->createComponent('SingleImage');
    }
    
    return $componentMapper->createComponent('ComboBox');
  }

  /**
   * Gibt zurück ob die EntityKlasse Psc\Image\Image implementiert
   * 
   * @var bool
   */
  public function isImage() {
    return $this->implementsInterface('Psc\Image\Image');
  }

  public function isCSEntry() {
    return $this->implementsInterface('Psc\TPL\ContentStream\Entry');
  }
}
