<?php

namespace Psc\CMS\Service;

use Psc\Net\ServiceRequest;
use Psc\Net\ServiceResponse;

/**
 * @group class:Psc\CMS\Service\StandardControllerService
 */
class StandardControllerServiceTest extends \Psc\Code\Test\ServiceBase {
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service\StandardControllerService';
    parent::setUp();
    $this->svc = new StandardControllerService(\Psc\PSC::getProject());
    $this->ctrlClass = __NAMESPACE__.'\\MyTestController';
    $this->svc->setControllerClass('test',$this->ctrlClass);
  }
  
  public function testExplicitRouteToIndex() {
    $this->assertRouteController(new ServiceRequest('GET',array('test','index')),
                                 $this->ctrlClass,
                                 'index',
                                 array()
                                );
  }

  public function testImlicitRouteToIndex() {
    $this->assertRouteController(new ServiceRequest('GET',array('test')),
                                 $this->ctrlClass,
                                 'index',
                                 array()
                                );
  }

  public function testRouteToMethod() {
    $this->assertRouteController(new ServiceRequest('GET',array('test','someMethod','value1','value2')),
                                 $this->ctrlClass,
                                 'someMethod',
                                 array('value1','value2')
                                );
  }

  public function testRouteMagicParams() {
    $this->assertRouteController($request = new ServiceRequest('GET',array('test','sophisticated','dynamic1','dynamic2'),'body-content'),
                                 $this->ctrlClass,
                                 'sophisticated',
                                 array('body-content','dynamic1','GET',array('dynamic1','dynamic2'), $request));

  }
}

class MyTestController {
  
  public function index() {
    
  }
  
  public function someMethod($param1,$param2) {
    
  }
  
  public function sophisticated($requestBody, $dynamic, $requestType, $requestParts, ServiceRequest $request) {
  }
}
?>