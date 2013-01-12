<?php

namespace Psc\Code\Generate;

class MyTestClassTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Generate\MyTestClass';
    parent::setUp();
  }
  
  public function testConstruct() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
  
  public function createMyTestClass() {
    return new MyTestClass();
  }
}
?>