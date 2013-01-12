<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class Aktion extends \Psc\XML\Object {
  
  /**
   * @xml\XmlAttribute(name="aktionart")
   */
  protected $art;
  
}
?>