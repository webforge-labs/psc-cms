<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class AnhangDaten extends \Psc\XML\Object {

  /**
   * @xml\XmlElement(name="anhanginhalt")
   */
  protected $data;
  
  public function getData() {
    return str_replace(array(' ',"\n","\r","\t"),'',$this->data);
  }
}

?>