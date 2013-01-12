<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class Flaechen extends \Psc\XML\Object {

  /**
   * @xml\XmlElement
   */
  protected $teilbar_ab;

  /**
   * @xml\XmlElement
   */
  protected $gesamtflaeche;
  
  /**
   * @xml\XmlElement
   */
  protected $bueroflaeche;
  
}

?>