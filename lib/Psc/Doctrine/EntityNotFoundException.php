<?php

namespace Psc\Doctrine;

use Psc\Code\Code;

class EntityNotFoundException extends Exception {
  
  public $findCriteria;

  public static function fromQuery($query) {
    $e = new static(
      sprintf("Es konnte kein Ergebnis gefunden werden. \n  DQL: '%s'\n  Parameter: %s",
              $query->getDQL(),
              Code::varInfo($findCriteria=$query->getParameters())
              )
    );
    $e->findCriteria = $findCriteria;
    return $e;
  }
  
  public static function criteria(Array $findCriteria) {
    $e = new static('Es konnte kein Ergebnis gefunden werden.');
    $e->findCriteria = $findCriteria;
    return $e;
  }

}
?>