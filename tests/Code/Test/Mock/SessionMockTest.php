<?php

namespace Psc\Code\Test\Mock;

/**
 * @group class:Psc\Code\Test\Mock\SessionMock
 */
class SessionMockTest extends \Psc\Code\Test\Base {
  
  protected $sessionMock;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Test\Mock\SessionMock';
    parent::setUp();
    $this->sessionMock = new SessionMock();
    $this->sessionMock->init();
    $this->sessionMock->set('key1','sub1','v1s1'); // man ist das häßlich, aber gut so hab ich das halt mal gemacht :)
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Psc\Session\Session', $this->sessionMock);
    
    $this->assertEquals('v1s1', $this->sessionMock->get('key1','sub1'));
  }
}
?>