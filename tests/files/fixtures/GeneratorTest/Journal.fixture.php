<?php

namespace ePaper42;

use Hitch\Mapping\Annotation AS Hitch;

/**
 * @Hitch\XmlObject
 */
class Journal extends \Psc\XML\Object {
  
  /**
   * @Hitch\XmlAttribute(name="title")
   */
  protected $title;
  
  /**
   * @Hitch\XmlAttribute(name="key")
   */
  protected $key;
  
  /**
   * @Hitch\XmlList(name="volume", type="ePaper42\Volume", wrapper="volumeList")
   */
  protected $volumes;
}
?>