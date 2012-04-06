<?php

namespace Psc\CMS;

class EntityViewPackageTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntityViewPackage';
    parent::setUp();
  }
  
  public function testConstruct() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
  
  public function createEntityViewPackage() {
    return new EntityViewPackage();
  }
}
?>