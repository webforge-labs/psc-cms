<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class Anhang extends \Psc\XML\Object {

  /**
   * @xml\XmlElement(name="anhangtitel")
   */
  protected $titel;

  /**
   * @xml\XmlElement()
   */
  protected $format;
  
  /**
   * @xml\XmlAttribute
   */
  protected $location;

  /**
   * @xml\XmlAttribute
   */
  protected $gruppe;
  
  /**
   * @xml\XmlElement(type="AnhangDaten")
   */
  protected $daten;
  
  public function getImageData() {
    return base64_decode($this->daten->getData());
  }
  
  public function getFormat() {
    if (trim($this->format) != '') 
      return mb_strtolower($this->format);
      
    return NULL;
  }
}

?>