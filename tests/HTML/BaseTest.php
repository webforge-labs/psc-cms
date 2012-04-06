<?php

namespace Psc\HTML;

class BaseTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\HTML\Base';
    parent::setUp();
  }
  
  public function testConstruct() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
  
  public function createBase() {
    return new Base();
  }
}
?>