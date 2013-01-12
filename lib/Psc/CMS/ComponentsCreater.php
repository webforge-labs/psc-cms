<?php

namespace Psc\CMS;

/**
 *
 * Der ComponentsCreater erstellt eine Componente, triggered dann das Event CREATED und setzt dann falls das event nicht processed wurde
 * die standard values und führt danach init() aus
 */
interface ComponentsCreater extends \Psc\Code\Event\Dispatcher {
  
  const EVENT_COMPONENT_CREATED = 'Psc\CMS\ComponentsCreater.ComponentCreated';
  
  public function subscribe(ComponentsCreaterListener $listener);
  
}
?>