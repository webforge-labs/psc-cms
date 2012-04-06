<?php

namespace Psc\CMS\Service;

class StandardControllerServiceTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service\StandardControllerService';
    parent::setUp();
  }
  
  public function testConstruct() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
  
  public function createStandardControllerService() {
    $this->service = $this->createMock('Psc\CMS\Service\StandardControllerService');
  }
}
?>