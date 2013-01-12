<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class Anbieter extends \Psc\XML\Object {

  /**
   * @xml\XmlType
   */
  protected $immobilie;  
}

?>