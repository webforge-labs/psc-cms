<?php

namespace Psc\CMS\Service;

class EntityURLGeneratorTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service\EntityURLGenerator';
    parent::setUp();
  }
  
  public function testConstruct() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
  
  public function createEntityURLGenerator() {
    return new EntityURLGenerator();
  }
}
?>