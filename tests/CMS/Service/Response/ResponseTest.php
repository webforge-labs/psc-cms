<?php

namespace Psc\CMS\Service\Response;

class ResponseTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service\Response\Response';
    parent::setUp();
  }
  
  public function testConstruct() {
    $response = new Response(\Psc\Net\Service::OK, array('blubb'=>'blubb'));
    $this->assertEquals(array('blubb'=>'blubb'), $response->getBody());
    $this->assertEquals(\Psc\Net\Service::OK, $response->getStatus());
    $this->assertInstanceOf('Psc\Net\ServiceResponse', $response);
  }
  
  public function createResponse() {
    return new Response();
  }
}
?>