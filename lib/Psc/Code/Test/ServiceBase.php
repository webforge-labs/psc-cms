<?php

namespace Psc\Code\Test;

use Psc\Net\Service;
use Psc\Net\ServiceRequest;
use Psc\Net\ServiceResponse;
use Closure;

class ServiceBase extends \Psc\Test\DatabaseTestCase {
  
  /**
   * Gibt einen ServiceRequest zurück
   * 
   * @return Psc\Net\ServiceRequest
   */
  public function rq(Array $parts, $method = Service::GET, $body = NULL) {
    return new ServiceRequest($method, $parts, $body);
  }
  
  public function assertRoutingException(Closure $c, $debugMessage = '') {
    try {
      $c();
    
    } catch (\Exception $e) {
      if ($e instanceof \Psc\CMS\Service\ControllerRouteException ||
          $e instanceof \Psc\Net\HTTP\HTTPException ||
          $e instanceof \Psc\Net\RequestMatchingException) {
        return;
      } else {
        $this->fail('Exception: Psc\CMS\Service\ControllerRouteException oder Psc\Net\RequestMatchingException erwartet. Es wurde aber '.\Psc\Code\Code::getClass($e).' gecatched. (ExceptionMessage: '.$e->getMessage().') '.$debugMessage);
      }
    }

    $this->fail('Exception: Psc\CMS\Service\ControllerRouteException oder Psc\Net\RequestMatchingException erwartet. Es wurde aber keine gecatched. '.$debugMessage);
  }

  public function assertRouteController($request, $expectedControllerClass, $expectedMethod, $expectedParams) {
    list($controller, $method, $params) = $this->svc->routeController($request);
    
    $this->assertInstanceof($expectedControllerClass, $controller);
    $this->assertEquals($expectedMethod, $method);
    $this->assertEquals($expectedParams, $params);
  }
}
?>