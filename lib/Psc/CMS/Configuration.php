<?php

namespace Psc\CMS;

use \Psc\DataInput,
    \Psc\ConfigMissingVariableException
;

class Configuration {
  
  protected $conf;
  
  public function __construct(Array $vars = array()) {
    $this->conf = new DataInput($vars);
  }
  
  public function get($keys, $default = NULL) {
    return $this->conf->get($keys, $default, $default);
  }
  
  public function req($keys, $default = NULL) {
    try {
      return $this->conf->get($keys, \Psc\DataInput::THROW_EXCEPTION, $default);
    } catch (\Psc\DataInputException $e) {
      throw new ConfigMissingVariableException($e->keys);
    }
  }
  
  public function set(Array $keys, $value) {
    return $this->conf->setDataWithKeys($keys,$value);
  }

  public function merge(Configuration $config, Array $fromKeys = array(), Array $toKeys = array()) {
    $this->conf->merge($config->getData(), $fromKeys, $toKeys);
    return $this;
  }
  
  /**
   * @return Psc\DataInput
   */
  public function getData() {
    return $this->conf;
  }
  
  /**
   * Setzt die Variable, wenn sie in der Configuration nicht gesetzt ist
   * wenn die Variable NULL ist wird sie auch gesetzt
   */
  public function setDefault($keys, $defaultValue) {
    $cought = FALSE;

    try {
      $value = $this->req($keys);
      
    } catch (ConfigMissingVariableException $e) {
      $cought = TRUE;
    }
    
    if ($cought || $value === NULL) {
      $this->conf->set($keys, $defaultValue);
    }
    
    return $this;
  }
}

?>