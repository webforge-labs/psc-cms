<?php

namespace Psc\Data;

/**
 *
 *
 * $usedValue = $cache->load('key', $loaded)
 * if (!$loaded) {
 *   $usedValue = //do whatever
 *
 *   $cache->store('key',$usedValue);
 * }
 */

interface Cache {
  
  /**
   *
   * @chainable
   * @param mixed $key wenn $key === NULL ist muss die implementierung entscheiden ob sie den Schlüssel für ein Objekt selbst versucht zu generieren oder eine Exception schmeisst
   * @return Cache
   */
  public function store($key, $value);
  
  /**
   * @return mixed
   */
  public function load($key, &$loaded);
  
  /**
   * @return bool
   */
  public function hit($key);

  /**
   * Entfernt die Value mit $key aus dem Cache
   *
   * ist der Key nicht gesetzt, wird keine Exception geworfen
   */
  public function remove($key);
}
?>