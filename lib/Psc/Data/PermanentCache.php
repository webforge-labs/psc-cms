<?php

namespace Psc\Data;

interface PermanentCache extends Cache {
  
  /**
   * Speichert den Cache im permanenten Speicher
   * 
   * @chainable
   */
  public function persist();
  
  /**
   * Lädt den Cache aus dem permanenten Speicher
   */
  public function init();

}
?>