<?php

namespace Psc;

/**
 * Decorator
 */
class PharVerboseAutoLoader extends PharAutoLoader {
  
  public function __construct() {
    $this->log('Lade PharAutoLoader');
    $this->dumpDebug = TRUE;
  }
  
  public function autoLoad($class) {
    $this->log('Versuche Klasse zu laden: "'.$class.'"');
    parent::autoLoad($class);
  }
  
  public function log($msg) {
    print '[PharAutoLoader] '.$msg."\n";
  }
  
}

?>