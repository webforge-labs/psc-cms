<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class Immo extends \Psc\XML\Object {
  
  /**
   * @xml\XmlType
   */
  protected $uebertragung;
  
  /**
   * @xml\XmlType
   */
  protected $anbieter;
}

?>