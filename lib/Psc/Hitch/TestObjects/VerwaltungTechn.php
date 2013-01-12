<?php

namespace Psc\Hitch\TestObjects;

use Hitch\Mapping\Annotation AS xml;

/**
 * @xml\XmlObject
 */
class VerwaltungTechn extends \Psc\XML\Object {
  
  /**
   * @xml\XmlElement
   */
  protected $objektnr_extern;
  
  /**
   * @xml\XmlElement
   */
  protected $stand_vom;
  
  /**
   * @xml\XmlType
   */
  protected $aktion;
}
?>