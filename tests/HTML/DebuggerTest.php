<?php

namespace Psc\HTML;

class DebuggerTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\HTML\Debugger';
    parent::setUp();
  }
  
  public function testConstruct() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
  
  public function createDebugger() {
    return new Debugger();
  }
}
?>