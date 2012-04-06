<?php

namespace Psc\Net\HTTP;

use Psc\Net\HTTP\RequestHandler;
use Psc\Net\ServiceRequest;
use Psc\Net\ServiceResponse;
use Psc\Net\Service;

/**
 * @group net-service
 */
class RequestHandlerTest extends \Psc\Code\Test\Base {
  
  protected $handler;
  
  protected $svc;

  public function setUp() {
    $this->chainClass = 'Psc\Net\HTTP\RequestHandler';
    $this->handler = new RequestHandler($this->svc = $this->createServiceMock(),
                                        $this->converter = $this->createResponseConverterMock()
                                        );
    parent::setUp();
  }
  
  public function testSelfMocking() {
    $this->setServiceIsResponsible($this->svc, FALSE);
    $this->assertFalse($this->svc->isResponsibleFor($this->createServiceRequest()));
  }
  
  public function testSelfMocking2() {
    $this->setServiceIsResponsible($this->svc, TRUE);
    $this->assertTrue($this->svc->isResponsibleFor($this->createServiceRequest()));
  }
  
  /**
   * @expectedException \Psc\Net\HTTP\NoServiceFoundException
   */
  public function testHandlerThrowsException_whenNoServiceIsFindable() {
    $this->setServiceIsResponsible($this->svc, FALSE); // gibt immer FALSE zurück
    
    $this->handler->findService($this->createServiceRequest());
  }
  
  public function testHandlerFindsService_andSetsService() {
    $this->setServiceIsResponsible($this->svc, TRUE);
    
    $service = $this->handler->findService($this->createServiceRequest());
    $this->assertSame($this->svc,$service);
  }
  
  public function testHandlerRoutesToService() {
    $this->setServiceIsResponsible($this->svc, TRUE);
    $this->expectServiceGetsRouted($this->svc);
    $this->expectConverterConverts(Response::create(200, 'mybody'));
    
    $this->handler->handle($this->doublesManager->createHTTPRequest('GET', '/episodes/8/form'));
    
    // service ist danach auf den Service gesetzt, der aufgerufen wurde
    $this->assertSame($this->svc, $this->handler->getService());
  }
  
  public function testHandlerReturnsResponse() {
    $this->setServiceIsResponsible($this->svc, TRUE);
    $this->expectServiceGetsRouted($this->svc);
    $this->expectConverterConverts($expectedResponse = Response::create(200, 'mybody'));
    
    $this->assertInstanceOf('Psc\Net\HTTP\Response',
                            $actualResponse = $this->handler->handle($this->doublesManager->createHTTPRequest('GET', '/episodes/8/form')));
    $this->assertSame($expectedResponse, $actualResponse);
  }

  /**
   * @dataProvider provideServiceExceptions
   */
  public function testHandlerReturnsResponse_evenIfException(\Exception $exception) {
    $this->setServiceIsResponsible($this->svc, TRUE);
    $this->expectServiceGetsRouted($this->svc);
    $this->setConverterConverts(Response::create(200, 'mybody'));
    
    $this->assertInstanceOf('Psc\Net\HTTP\Response',
                            $response = $this->handler->handle($this->doublesManager->createHTTPRequest('GET', '/episodes/8/form')));
    $this->assertGreaterThan(0, $response->getCode());
  }

  public function testRunController_convertsValidatorException_to400() {
    // controller throws an ValidatorException
    $validatorException = new \Psc\Form\ValidatorException('Ich habe einen User-Fehler verursacht');
    $validatorException->field = 'unknown';
    $validatorException->data = NULL;
    $validatorException->label = 'Unknown';

    $this->setServiceIsResponsible($this->svc, TRUE);
    $this->expectServiceThrowsExceptionWhileRouting($this->svc, $validatorException);

    $this->assertInstanceof('Psc\Net\HTTP\Response',
                            $res = $this->handler->handle($this->doublesManager->createHTTPRequest('GET', '/episodes/8/form')));
    $this->assertEquals(400, $res->getCode());
  }
  
  public function testServiceCollection() {
    $this->assertInternalType('array',$this->handler->getServices());
    $this->assertEquals(array($this->svc), $this->handler->getServices());
  }
  
  public function testServiceCollection_serviceNotTwice() {
    $this->assertChainable($this->handler->addService($this->svc));
    $this->assertCount(1,$this->handler->getServices());
  }

  public function testServiceCollection_addsOtherService() {
    $this->assertChainable($this->handler->addService($snd = $this->createServiceMock()));
    $this->assertNotSame($this->svc, $snd);
    $this->assertCount(2,$this->handler->getServices());
  }
  
  public function testCreateServiceRequest_OverridenRequestMethod() {
    $httpRequest = $this->doublesManager->createHTTPRequest('POST', '/episodes/8/form', array(), array('X-Psc-Cms-Request-Method'=>'PUT'));
    $httpRequest->setHeaderField('X-Psc-Cms-Request-Method', 'PUT');
    
    $serviceRequest = $this->handler->createServiceRequest($httpRequest);
    $this->assertInstanceOf('Psc\Net\ServiceRequest', $serviceRequest);
    $this->assertEquals(Service::PUT, $serviceRequest->getType());
    $this->assertObjectNotHasAttribute('X-Psc-Cms-Request-Method', $serviceRequest->getBody());
  }
  
  protected function createServiceMock() {
    $mock = $this->getMock('Psc\Net\Service');
    
    return $mock;
  }
  
  protected function setServiceIsResponsible($mock, $is) {
    $mock->expects($this->once())
         ->method('isResponsibleFor')
         ->will($this->returnValue($is));
  }
  
  protected function expectServiceGetsRouted($mock) {
    $mock->expects($this->once())
         ->method('route')
         ->will($this->returnValue(new ServiceResponse(Service::OK)));
  }
  
  protected function expectServiceThrowsExceptionWhileRouting($mock, \Exception $exception) {
    $mock->expects($this->once())
         ->method('route')
         ->will($this->throwException($exception));
  }

  protected function createResponseConverterMock() {
    $mock = $this->getMock('Psc\Net\HTTP\ResponseConverter',array('fromService'));
    
    return $mock;
  }
  
  protected function setConverterConverts(\Psc\NET\HTTP\Response $response) {
    $this->converter->expects($this->any())->method('fromService')
                    ->will($this->returnValue($response));
  }

  protected function expectConverterConverts(\Psc\NET\HTTP\Response $response) {
    $this->converter->expects($this->atLeastOnce())->method('fromService')
                    ->will($this->returnValue($response));
  }

  protected function createServiceRequest () {
    return new ServiceRequest(Service::GET, array('episodes','8','form'));
  }
  
  public static function provideServiceExceptions() {
    return Array(
      array(\Psc\Net\HTTP\HTTPException::NotFound()),
      array(\Psc\Net\HTTP\HTTPException::BadRequest()),
      array(new \Exception('Manno')),
      array(new \Psc\Exception('I am faling'))
    );
  }
}
?>