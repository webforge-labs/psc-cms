<?php

namespace Psc\Net;

use Psc\Net\ServiceRequest;

/**
 * @group class:Psc\Net\ServiceRequest
 * @group net-service
 */
class ServiceRequestTest extends \Psc\Code\Test\Base {
  
  protected $request;

  public function setUp() {
    $this->chainClass = 'Psc\Net\ServiceRequest';
    parent::setUp();

    $this->request = new ServiceRequest(
      Service::PUT,
      array('entities', 'tag', 1),
      $body = NULL,
      array(),
      $files = array(),
      $meta = array('revision'=>'1147')
    );
  }

  public function testConstruct() {
    $sr = $this->createServiceRequest(Service::GET,array('episodes','8','form'), 'myBody');
    $this->assertEquals(Service::GET,$sr->getType());
    $this->assertEquals(array('episodes','8','form'),$sr->getParts());
    $this->assertEquals('myBody',$sr->getBody());
    
    return $sr;
  }
  
  public function testBodyDefaultsNULL() {
    $this->assertNull($this->createServiceRequest(Service::GET, array('egal'))->getBody());
  }
  
  /**
   * @depends testConstruct
   */
  public function testCreate($expectedServiceRequest) {
    $serviceRequest = ServiceRequest::create(Service::GET,array('episodes','8','form'), 'myBody');
    $this->assertEquals($expectedServiceRequest, $serviceRequest);
  }
  
  public function testDataSetterAndGetter() {
    $this->createServiceRequest(Service::POST, array('episodes','8','form'));
    $this->test->getter('parts', new \Psc\Data\Type\ArrayType());
    $this->test->setter('parts', new \Psc\Data\Type\ArrayType());

    $this->test->getter('body', new \Psc\Data\Type\ArrayType());
    $this->test->setter('body', new \Psc\Data\Type\ArrayType());
    $this->test->getter('body', new \Psc\Data\Type\StringType());
    $this->test->setter('body', new \Psc\Data\Type\StringType());
  }
  
  /**
   * @expectedException Psc\Code\WrongValueException
   */
  public function testTypeGetter_wrongValues() {
    $sr = $this->createServiceRequest(Service::POST, array('episodes','8','form'));
    $sr->setType('wrong');
  }

  public function testTypeGetter_rightValues() {
    $sr = $this->createServiceRequest(Service::POST, array('episodes','8','form'));
    $sr->setType(Service::POST);
    $sr->setType(Service::GET);
    $sr->setType(Service::PUT);
    $sr->setType(Service::DELETE);
    $sr->setType(Service::PATCH);
  }
  
  public function testCanHaveMetaAttached_WhichCanBeRetrieved() {
    $this->assertTrue(
      $this->request->hasMeta('revision')
    );
    
    $this->assertEquals(
      1147,
      $this->request->getMeta('revision')
    );
    
    $this->assertNull($this->request->getMeta('rewision'));
  }
  
  public function testCanSetMeta() {
    $this->assertChainable($this->request->setMeta('something', 'value'));
    
    $this->assertTrue($this->request->hasMeta('something'));
    $this->assertEquals('value', $this->request->getMeta('something'));
  }

  public function createServiceRequest($type, $data, $body = NULL) {
    return $this->test->object = new ServiceRequest($type,$data, $body);
  }
}
?>