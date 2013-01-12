<?php

namespace Psc\Code\Event;

/**
 * Wird von einem Objekt implementiert, welches Events feuert
 *
 * andere Subscriber können dann zum Dispatcher gehen und sich dort binden()
 * wenn dann der Dispatcher ein Event dispatch() wird beim Subscriber trigger() mit dem entsprechenden Event aufgerufen
 */
interface Dispatcher {
  
  /**
   * @return Psc\Code\Event\Manager
   */
  public function getManager();
}