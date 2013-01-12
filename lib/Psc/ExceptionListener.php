<?php

namespace Psc;

/**
 * Führt irgendwo eine Try/Catch Aktion aus und kann dann Objekte die sich um eine bestimmte Exception kümmern wollen benachrichtigen
 */
interface ExceptionListener {

  /**
   * gibt eine Exception oder diese Exception zurück
   *
   * kann z. B. im Controller dafür verwendet werden eine uniqueConstraintException in eine ValidationException umzuwandeln, wenn erwartet wird, dass der unique_constraint failen kann
   */
  public function listenException(\Exception $e);

}

?>