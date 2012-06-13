<?php

namespace Psc\CMS\Service\Response;

/**
 * @group class:Psc\CMS\Service\Response\ExceptionResponse
 */
class ExceptionResponseTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service\Response\ExceptionResponse';
    parent::setUp();
  }
  
  public function testConstruct() {
    $response = new ExceptionResponse($e = new \Psc\Exception('testmessage'));
    $this->assertChainable($response);
    $this->assertSame($e, $response->getBody());
  }
  
  public function createExceptionResponse() {
    return new ExceptionResponse();
  }
}
?>