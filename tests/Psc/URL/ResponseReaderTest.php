<?php

namespace Psc\URL;

/**
 * @group class:Psc\URL\ResponseReader
 */
class ResponseReaderTest extends \Psc\Code\Test\Base {
  
  protected $responseReader;
  
  public function setUp() {
    $this->chainClass = 'Psc\URL\ResponseReader';
    parent::setUp();
    $this->responseReader = new ResponseReader();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('TODO');
  }
}
?>