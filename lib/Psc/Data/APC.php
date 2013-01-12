<?php

namespace Psc\Data;

class APC extends \Psc\Object implements Cache {
  
  protected $prefix;
  
  public function __construct($prefix = NULL) {
    if (!extension_loaded('apc')) {
      throw new APCNotLoadedException('APC Extension nicht geladen');
    }
    if (PHP_SAPI === 'cli' && ini_get('apc.enable_cli') == FALSE ) {
      throw new APCNotEnabledException('APC ist nicht geladen (CLI). apc.enable_cli = '.ini_get('apc.enable_cli'));
    }
    
    if (ini_get('apc.enabled') == FALSE ) {
      throw new APCNotEnabledException('APC ist nicht geladen. apc.enabled = '.ini_get('apc.enabled'));
    }
  }
  
  /**
   *
   * @chainable
   * @return Cache
   */
  public function store($key, $value) {
    $key = $this->getKey($key);
    $ret = apc_store($key, $value, 0); // store overrides, add nicht
    
    if ($ret === FALSE) {
      throw new \Psc\Exception('Cannot store value for '.$key.' into APC');
    }
    
    return $this;
  }
  
  /**
   * @return mixed
   */
  public function load($key, &$loaded) {
    $key = $this->getKey($key);
    return apc_fetch($key, $loaded);
  }
  
  /**
   * @return bool
   */
  public function hit($key) {
    $key = $this->getKey($key);
    return apc_exists($key);
  }
  
  public function remove($key) {
    $key = $this->getKey($key);
    return apc_delete($key);
  }

  protected function getKey($key) {
    if (is_array($key)) $key = implode(':',$key);
    $key = (string) $key;
    if (mb_strlen($key) === 0) throw new \InvalidArgumentException('Schlüssel muss entweder ein Array oder ein nichtleerer String sein');
    return $this->prefix.':'.$key;
  }
}