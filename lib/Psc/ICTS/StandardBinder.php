<?php

namespace Psc\ICTS;

use \Psc\Exception;

class StandardBinder extends \Psc\Object implements Binder {
    
  /**
   * @var \Psc\ICTS\Data
   */
  protected $data;
  
  /**
   * @var array
   */
  protected $prefix = array(); 
  
  public function __construct(&$data) {
    $this->data = new Data($data);
  }
  
  /**
   * OHNE prefix
   */
  public function bind($keys, $value) {
    if (func_num_args() > 2) throw new \Psc\Exception('Ein Dritter Parameter wird nicht interpretiert. Es gibt nur $keys (Array) und $value');
    return $this->set($keys, $value, self::ABSOLUTE_KEYS);
  }

  public function b($keys, $default = NULL) {
    return $this->get($keys, $default, self::ABSOLUTE_KEYS, $default);
  }
  
  /**
   * MIT prefix
   * @param $default kann auch Data::THROW_EXCEPTION sein
   */
  public function g($keys, $default = NULL) {
    return $this->get($keys, $default, self::PREFIX_KEYS, $default);
  }

  /**
   * MIT prefix
   */
  public function s($keys, $value) {
    if (func_num_args() > 2) throw new \Psc\Exception('Ein Dritter Parameter wird nicht interpretiert. Es gibt nur $keys (Array) und $value');
    $this->set($keys, $value, self::PREFIX_KEYS);
    return $this;
  }
  
  /**
   * MIT Prefix
   *
   * der Default wird gesetzt, wenn der Wert von keys === NULL Ist oder die Keys nicht gesetzt sind
   */
  public function setDefault($keys, $defaultValue, $prefix = self::ABSOLUTE_KEYS) {
    if ($this->get($keys, NULL, $prefix) === NULL) {
      $this->set($keys, $defaultValue, $prefix);
    }
    return $this;
  }
  
  public function get($keys, $do = self::THROW_EXCEPTION, $prefix = self::ABSOLUTE_KEYS, $default = NULL) {
    $keys = ($prefix == self::PREFIX_KEYS) ? array_merge($this->prefix,(array) $keys) : $keys;
    
    try {
      return $this->data->get($keys, self::THROW_EXCEPTION, $default); // das 2te $do ist hier der default, wenn data von keys leer ist
    } catch (\Psc\DataInputException $e) {
      
      if ($do === self::THROW_EXCEPTION)
        $this->processDataInputException($e);
        
      return $do;
    }
  }

  public function set($keys, $value, $prefix = self::ABSOLUTE_KEYS) {
    $keys = ($prefix == self::PREFIX_KEYS) ? array_merge($this->prefix,(array) $keys) : $keys;
    
    return $this->data->set($keys, $value);
  }
  
  /**
   * OHNE Prefix
   */
  public function getBinds() {
    return $this->data;
  }

  /**
   * OHNE Prefix
   * ersetzt alle Binds im Binder
   */
  public function setBindsFromArray(Array &$data) {
    $this->data->setData($data);
    return $this;
  }

  
  /**
   * darf auch ein leerer Array sein
   */
  public function getPrefix() {
    return (array) $this->prefix;
  }
  
  /**
   * Fügt dem Prefix ein neues Element hinzu
   * 
   */
  public function addPrefix($prefix) {
    $this->prefix[] = $prefix;
    return $this;
  }
  
  public function removePrefix() {
    array_pop($this->prefix);
    return $this;
  }
  
  public function processDataInputException($e) {
    throw new Exception('Binding Error: /'.implode('.',$e->keys).' ist nicht auffindbar. aktueller Prefix ist: "'.implode('.',$this->prefix).'". array_keys($this->data): '.$this->getDebug());
  }
  
  public function getDebug() {
    if (!function_exists('\Psc\ICTS\my_array_keys_recursive')) {
      function my_array_keys_recursive($array) {
      $ret = array();
      
      foreach ($array as $key=>$value) {
        if (is_array($value))
          $ret[$key] = my_array_keys_recursive($value);
        else
          $ret[$key] = gettype($value);

      }

      return $ret;
      }
    }
    $keysMap = my_array_keys_recursive($this->data->getData());

    return print_r($keysMap,true);
  }
  
  public function debug() {
    print 'Binder Debug:'."\n";
    print 'Prefix: "'.implode(',',$this->prefix).'"'."\n";
    print $this->getDebug();
  }
  
  public function __sleep() {
    return array('data','prefix');
  }
}

?>