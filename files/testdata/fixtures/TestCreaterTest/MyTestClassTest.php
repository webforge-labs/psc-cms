<?php

namespace Psc\Code\Generate;

class MyTestClassTest extends \Psc\Code\Test\Base {
  
  protected $myTestClass;
  
  public function setUp() {
    //$this->chainClass = 'Psc\Code\Generate\MyTestClass';
    parent::setUp();
    $this->myTestClass = new MyTestClass();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>