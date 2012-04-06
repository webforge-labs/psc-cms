<?php

namespace Psc\Entities;

class UserTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Entities\User';
    parent::setUp();
  }
  
  public function testConstruct() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
  
  public function createUser() {
    return new User();
  }
}
?>