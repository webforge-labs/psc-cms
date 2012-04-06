<?php

namespace Psc\Net;

class ServiceErrorPackageTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Net\ServiceErrorPackage';
    parent::setUp();
  }
  
  public function testConstruct() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
  
  public function createServiceErrorPackage() {
    return new ServiceErrorPackage();
  }
}
?>