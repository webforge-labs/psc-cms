<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class Freitexte extends \Psc\XML\Object {

  /**
   * @xml\XmlElement
   */
  protected $objekttitel;
  
  /**
   * @xml\XmlElement
   */
  protected $dreizeiler;
  
  /**
   * @xml\XmlElement
   */
  protected $lage;

  /**
   * @xml\XmlElement
   */
  protected $ausstatt_beschr;
  
  /**
   * @xml\XmlElement
   */
  protected $objektbeschreibung;

  /**
   * @xml\XmlElement
   */
  protected $sonstige_angaben;
}

?>