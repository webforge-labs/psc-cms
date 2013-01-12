<?php

namespace Psc\CMS\Service;

use Psc\Net\ServiceRequest;
use Psc\Net\Service;
use Psc\Net\RequestMatcher;
use Psc\CMS\Controller\ServiceController;

class StandardControllerService extends \Psc\CMS\Service\ControllerService {

  /**
   * Findet den Controller anhand des Requests
   *
   * unsere StandardStrategie ist hier:
   *   1.Part => Klassenname: $className in <$this->getControllersNamespace()>\\<ucfirst($className)>Controller
   *                oder (nicht implementiert) 1.Part => Schlüssel in $this->controllersMap
   *                oder (whatever)
   *   2.Part => Name der Methode des Controllers
   *   3-x.Part => Parameter zur Methode des Controllers (Reihenfolge wichtig)
   *
   *   die "freien" Parameter des Requests werden auf die freien Parameter der Methode gemappt. Zusätzlich können die Parameter
   *   der Methode noch $requestType|$requestParts|$requestBody|$request sein. Diese werden dann mit den Parametern des Requests gesetzt
   *
   *   GET /person/form/7
   *   PersonController::form = function($id)
   * =>
   *   Project\Controllers\PersonController::form(7)
   *
   *   GET /person/form/7
   *   PersonController::form = function($requestType, $id)
   * =>
   *   Project\Controllers\PersonController::form(Service::GET, 7)
   * 
   *   POST /person/form/7
   *   PersonController::form = function($requestType, $requestBody, $id)
   * =>
   *   Project\Controllers\PersonController::form(Service::POST, array(...), 7)
   * 
   *   GET /person/complex/example/for/request/uri
   *   PersonController::complex = function($requestType, $requestParts)
   * =>
   *   Project\Controllers\PersonController::complex(Service::GET, array('example','for','request','uri'))
   * 
   * @return list(\Psc\CMS\Controller\ServiceController, string, Array)
   */
  public function routeController(ServiceRequest $request) {
    $parts = $request->getParts();
    $r = new RequestMatcher($request);
    
    /* Class */
    $class = $this->getControllerClass($r->matchNES(), $check = TRUE);

    /* Method */
    $method = $r->shift(); // method ist optional (kann index sein)
    if ($method === NULL) {
      $method = 'index';
    }
    $class->elevateClass();
    
    if (!$class->hasMethod($method)) {
      throw ControllerRouteException::create("in Methode: %s::%s nicht gefunden", $class->getFQN(), $method);
    }
    
    /* Params */
    $gMethod = $class->getMethod($method);
    $dynamicParts = $r->getLeftParts(); // absaven
    $params = array();
    foreach ($gMethod->getParameters() as $param) {
      if ($param->getName() === 'requestBody') {
        $params[] = $request->getBody();
      } elseif ($param->getName() === 'requestType') {
        $params[] = $request->getType();
      } elseif ($param->getName() === 'request') {
        $params[] = $request;
      } elseif ($param->getName() === 'requestParts') {
        $params[] = $dynamicParts;
      } elseif (!$r->isEmpty()) {
        $params[] = $r->shift();
      }
    }
    return array($this->getControllerInstance($class), $method, $params);
  }
}
?>