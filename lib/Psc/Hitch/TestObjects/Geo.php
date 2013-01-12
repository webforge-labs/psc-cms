<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class Geo extends \Psc\XML\Object {

  /**
   * @xml\XmlElement
   */
  protected $regionaler_zusatz;
}

?>