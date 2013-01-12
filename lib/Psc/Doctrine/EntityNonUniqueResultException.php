<?php

namespace Psc\Doctrine;

use Psc\Code\Code;

class EntityNonUniqueResultException extends Exception {
  
  public $findCriteria;

  public static function fromQuery($query) {
    $e = new static(
      sprintf("Es wurden mehrere Ergebnisse gefunden, obwohl nur 1 erwartet war. \n  DQL: '%s'\n  Parameter: %s",
              $query->getDQL(),
              Code::varInfo($findCriteria=$query->getParameters())
              )
    );
    $e->findCriteria = $findCriteria;
    return $e;
  }
  
}

?>