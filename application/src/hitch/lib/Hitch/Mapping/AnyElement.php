<?php

namespace Hitch\Mapping;

use \SimpleXMLElement;

class AnyElement extends \Psc\Object {

  /**
   * @var \SimpleXMLElement
   */
  protected $xml;
  
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var mixed
   */
  protected $value;
  
  /**
   * @var array
   */
  protected $attributes;
  
  public function __construct($name, $value, Array $attributes, SimpleXMLElement $xml) {
    $this->name = $name;
    $this->value = $value;
    $this->attributes = $attributes;
    $this->xml = $xml;
  }
  
}
?>