<?php

namespace Psc\URL\Service;

use \Psc\Object;

class Call extends \Psc\Object {
  
  const UNDEFINED = '____PARAMETER_IS_UNDEFINED____';
  
  protected $parameters = array();
  
  protected $name;
  
  public function __construct($name, Array $parameters = array()) {
    $this->parameters = $parameters;
    $this->name = $name;
  }
  
  public function getParameter($index) {
    return array_key_exists($index, $this->parameters) ? $this->parameters[$index] : self::UNDEFINED;
  }
  
  public function addParameter($value) {
    $this->parameters[] = $value;
    return $this;
  }
}
?>