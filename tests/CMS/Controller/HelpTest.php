<?php

namespace Psc\CMS\Controller;

class HelpTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\Help';
    parent::setUp();
  }
  
  public function testSubscribeException() {
    // @TODO
  }
  
  public function testConstruct() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
  
  public function createHelp() {
    return new Help();
  }
}
?>