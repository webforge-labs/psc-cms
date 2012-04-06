<?php

namespace Psc\Code\Test;

class MockTest extends \Psc\Code\Test\Base {
  
  protected $mock;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Test\Mock';
    parent::setUp();
    $this->mock = new Mock();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>