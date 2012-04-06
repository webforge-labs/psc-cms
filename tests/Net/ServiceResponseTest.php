<?php

namespace Psc\Net;

use Psc\Net\ServiceResponse;
use Psc\Data\Type\StringType;
use Psc\Data\Type\ArrayType;

/**
 * @group net-service
 */
class ServiceResponseTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\Net\ServiceResponse';
    parent::setUp();
  }

  public function testConstruct() {
    $response = $this->createServiceResponse(Service::OK);
    $this->assertEquals(Service::OK, $response->getStatus());

    $response = $this->createServiceResponse(Service::ERROR);
    $this->assertEquals(Service::ERROR, $response->getStatus());
    
    $this->test->constructor($this->chainClass, array('status'=>Service::OK, 'body'=>$this->getType('String')));
    $this->test->constructor($this->chainClass, array('status'=>Service::ERROR, 'body'=>$this->getType('Array')));
  }
  
  public function testStatus() {
    $response = $this->createServiceResponse();
    $this->assertChainable($response->setStatus(Service::OK));
    $this->assertChainable($response->setStatus(Service::ERROR));
  }
  
  /**
   * @expectedException Psc\Code\WrongValueException
   */
  public function testStatus_wrongValuesForSetter() {
    $this->createServiceResponse()->setStatus('wrong');
  }

  /**
   * @expectedException Psc\Code\WrongValueException
   */
  public function testStatus_wrongValuesForConstructor() {
    $this->test->constructor($this->chainClass, array('status'=>'none', 'body'=>$this->getType('String')));
  }
  
  public function testBody() {
    $this->test->object = $this->createServiceResponse();
    $this->test->setter('body',$this->getType('String'));
    $this->test->setter('body',$this->getType('Array'));
  }

  public function createServiceResponse($status = Service::OK) {
    return new ServiceResponse($status);
  }
}
?>