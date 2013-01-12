<?php

namespace Psc\XML;

class Object extends \Psc\Object implements \Hitch\MetadataObject {
  
  protected $__metadata;
  
  public function setMetadata($meta) {
    $this->__metadata = $meta;
  }
  
  public function getMetaData() {
    return $this->__metadata;
  }
  
  /**
   * Gibt ein XML SubElement des Elementes zurück
   *
   * Dies kann auch ein Basic-Datenwert werden
   * wird vom XML Mapper benutzt
   */
  public function getElement($name) {
    return $this->callGetter($name);
  }
}
?>