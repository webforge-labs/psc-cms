<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class Objektkategorie extends \Psc\XML\Object {
  
  /**
   * @xml\XmlAnyElement(list=true)
   * @var array
   */
  protected $objektart;
  
  public function addAnyElement(\Hitch\Mapping\AnyElement $element) {
    $this->objektart[$element->getName()] = $element; 
  }
  
}

?>