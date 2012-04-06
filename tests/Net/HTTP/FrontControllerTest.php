<?php

namespace Psc\Net\HTTP;

use Psc\Net\HTTP\FrontController;

/**
 * @group net-service
 */
class FrontControllerTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Net\HTTP\FrontController';
    parent::setUp();
  }

  public function testInit_noConstructorVars() {
    $fc = $this->createFrontController();
    
    $this->assertChainable($fc->init($this->doublesManager->createHTTPRequest('GET','/klimm/bimm')));
    
    // RequestHandler + ResourceHandler sind gesetzt
    $this->assertInstanceof('Psc\Net\HTTP\RequestHandler',$fc->getRequestHandler());
    $this->assertInstanceof('Psc\CMS\ResourceHandler',$fc->getResourceHandler());
    
    // Psc\CMS\Service ist der Default-Service sozusagen
    $services = $fc->getRequestHandler()->getServices();
    $this->assertCount(1,$services);
    $this->assertInstanceOf('Psc\CMS\Service', current($services));
  }
  
  public function testIfResourceHandlerFindsResponse_RequestHandlerIsNotUsed() {
    $fc = $this->createFrontController();
    
    $rq = $this->createRequestHandlerMock();
    $rq->expects($this->never())
         ->method('handle')
        ;
         
    $rh = $this->createResourceHandlerMock();
    $rh->expects($this->once())
      ->method('handle')
      ->will($this->returnValue(\Psc\Net\HTTP\Response::create(200),'body { padding: 0px; }'));
    
    $fc = new FrontController($rq, $rh);
    $fc->init($request = $this->doublesManager->createHTTPRequest('GET','/css/reset.css'));
    
    $fc->handle($request);
  }
  
  public function testFrontcontrollerReturnsResponse_evenExceptionHappens() {
    $mock = $this->createRequestHandlerMock();
    $mock->expects($this->once())
         ->method('handle')
         ->will($this->throwException(new \Psc\Exception('Diese Exception ist sehr schwerwiegend und sollte durch den Frontcontroller gefangen werden')));
    
    $fc = new FrontController($mock);
    $fc->init($this->doublesManager->createHTTPRequest('GET','/klimm/bimm/bumm'));
    
    $this->assertInstanceOf('Psc\Net\HTTP\Response',$response = $fc->handle($fc->getRequest()));
    $this->assertEquals(500,$response->getCode(), $response->debug());
  }

  
  protected function createFrontController() {
    return new FrontController();
  }
  
  protected function createRequestHandlerMock() {
    return $this->getMock('Psc\Net\HTTP\RequestHandler', array(), array($this->getMock('Psc\Net\Service')));
  }

  protected function createResourceHandlerMock() {
    return $this->getMock('Psc\CMS\ResourceHandler', array(), array($this->getMock('Psc\CMS\ResourceManager')));
  }
}
?>