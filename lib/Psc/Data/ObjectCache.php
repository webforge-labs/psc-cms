<?php

namespace Psc\Data;

use \Psc\Data\Cache;
use \Closure;
use \Psc\DataInput;

/**
 *
 * CacheKeys (hier als $keys) ist ein array von Identifiern die ein Objekt eindeutig indizieren
 *
 * die CacheKeys müssen mit der $hashFunction von einem Objekt erzeugt werden können
 */
class ObjectCache extends \Psc\Object implements \Psc\Data\Cache {
  
  /**
   * @var Psc\DataInput
   */
  protected $cachedObjects;
  
  /**
   * Time To Live der Items
   * 
   * @var int in Sekunden
   */
  protected $ttl;
  
  /**
   * @var Closure 1 Parameter der das objekt ist
   */
  protected $hashFunction;

  /**
   *
   * @param Closure $hashFunction die Hashfunktion hat einen Parameter und gibt einen array von CacheKeys für das Objekt zurück
   */
  public function __construct(Closure $hashFunction, $ttl) {
    $this->cachedObjects = new DataInput();
    $this->hashFunction = $hashFunction;
    $this->ttl = $ttl;
  }
  
  public function store($key, $value) {
    $object = $value;
    $keys = $key ?: $this->getKeys($object);
    $this->cachedObjects->set($keys, array('object'=>$object,
                                           'expire'=>$this->now()+$this->getTTL($object)
                                          ));
    return $this;
  }
  
  
  public function load($key, &$loaded) {
    $cached = $this->cachedObjects->get($key, NULL, NULL);
    
    if (!is_array($cached)) {
      $loaded = FALSE;
      return NULL;
    }
    
    if ($cached['expire'] < $this->now()) { // $cache eintrag ist ausgelaufen
      $this->cachedObjects->remove($key);
      $loaded = FALSE;
      return NULL;
    }
    
    $loaded = TRUE;   
    return $cached['object'];
  }

  public function hit($key) {
    $loaded = FALSE;
    $this->load($key,$loaded);
    return $loaded;
  }
  
  public function remove($key) {
    $this->cachedObjects->remove($key);
    return $this;
  }
  
  /**
   * @return array
   */
  public function getKeys($object) {
    $h = $this->hashFunction;
    return $h($object);
  }
  
  /**
   * Gibt die aktuelle Zeit zurück
   *
   * die Funktion ist fürs testen eine echte Hilfe
   */
  protected function now() {
    return time();
  }

  protected function getTTL($object) {
    return $this->ttl;
  }
}
?>