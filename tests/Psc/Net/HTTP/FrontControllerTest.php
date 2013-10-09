<?php

namespace Psc\Net\HTTP;

use Psc\Net\HTTP\FrontController;

/**
 * @group class:Psc\Net\HTTP\FrontController
 * @group net-service
 */
class FrontControllerTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Net\HTTP\FrontController';
    parent::setUp();
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
}
?>