<?php

namespace Psc\CMS\Service\Response;

use Psc\Form\ValidatorException;

/**
 * @group class:Psc\CMS\Service\Response\ValidationResponse
 */
class ValidationResponseTest extends \Psc\Code\Test\Base {
  
  protected $response;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service\Response\ValidationResponse';
    parent::setUp();
    $this->response = $this->createValidationResponse();
  }
  
  public function testConstruct() {
    $this->assertChainable($this->response);
    $this->assertInstanceOf('Psc\Form\ValidatorException', $this->response->getBody());
  }
  
  public function createValidationResponse() {
    return new ValidationResponse(new ValidatorException('leer'));
  }
}
?>