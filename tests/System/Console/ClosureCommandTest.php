<?php

namespace Psc\System\Console;

/**
 * @group class:Psc\System\Console\ClosureCommand
 */
class ClosureCommandTest extends \Psc\Code\Test\Base {
  
  protected $closureCommand;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Console\ClosureCommand';
    parent::setUp();
    //$this->closureCommand = new ClosureCommand();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>