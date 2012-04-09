<?php

namespace ePaper42;

use Hitch\Mapping\Annotation AS Hitch;

/**
 * @Hitch\XmlObject
 */
class Volume extends \Psc\XML\Object {
  
  /**
   * @Hitch\XmlAttribute(name="title")
   */
  protected $title;
  
  /**
   * @Hitch\XmlAttribute(name="key")
   */
  protected $key;
  
  /**
   * @Hitch\XmlList(name="issue", type="ePaper42\Issue", wrapper="issueList")
   */
  protected $issues;
}
?>