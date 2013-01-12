<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class Preise extends \Psc\XML\Object {

  /**
   * @xml\XmlElement
   */
  protected $aussen_courtage;
  
  /**
   * @xml\XmlElement
   */
  protected $mietpreis_pro_qm;

  /**
   * @xml\XmlElement
   */
  protected $nebenkosten;
}

?>