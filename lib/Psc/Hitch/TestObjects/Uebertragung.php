<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class Uebertragung extends \Psc\XML\Object {
  
  /**
   * @xml\XmlAttribute
   */
  protected $art;
  
  /**
   * @xml\XmlAttribute
   */
  protected $umfang;
  
  /**
   * @xml\XmlAttribute
   */
  protected $version;
  
  /**
   * @xml\XmlAttribute
   */
  protected $sendersoftware;
  
  /**
   * @xml\XmlAttribute
   */
  protected $techn_email;
  
}

?>