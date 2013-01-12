<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class Objektart extends \Psc\XML\Object {
  
  /**
   * @xml\XmlList(name="")
   */
  protected $contents;
    
}

?>