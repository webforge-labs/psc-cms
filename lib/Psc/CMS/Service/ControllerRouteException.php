<?php

namespace Psc\CMS\Service;

use Psc\Net\ServiceRequest;
use Psc\Code\Generate\GClass;

class ControllerRouteException extends \Psc\CMS\Service\Exception {
  
  public static function missingController(GClass $controllerClass, $exception) {
    return new static(sprintf("Controller der Klasse: '%s' nicht gefunden. ".$exception->getMessage(), $controllerClass->getFQN()));
  }
  
  public static function wrongType(ServiceRequest $request) {
    return new static(sprintf('Invalider Request-Type: '.$request->getType()));
  }
}
?>