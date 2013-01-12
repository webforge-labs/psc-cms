<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class Kontaktperson extends \Psc\XML\Object {

  /**
   * @xml\XmlElement
   */
  protected $email_direkt;
}

?>