<?php

namespace Psc;

/**
 * Führt irgendwo eine Try/Catch Aktion aus und kann dann Objekte die sich um eine bestimmte Exception kümmern wollen benachrichtigen
 */
interface ExceptionDelegator {
  
  public function subscribeException($exception, ExceptionListener $listener);

}

?>