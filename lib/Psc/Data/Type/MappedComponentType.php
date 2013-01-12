<?php

namespace Psc\Data\Type;

interface MappedComponentType {

  /**
   * am besten ist es den $componentMapper zu benutzen, um die Componente zu instanziieren:
   *
   *  return $componentMapper->createComponent('BirthdayPicker');
   * 
   * @return Psc\CMS\Component
   */
  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper);
  
}
?>