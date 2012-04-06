<?php

namespace Psc\Code\Test\Mock;

class SessionMockTest extends \Psc\Code\Test\Base {
  
  protected $sessionMock;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Test\Mock\SessionMock';
    parent::setUp();
    $this->sessionMock = new SessionMock();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>