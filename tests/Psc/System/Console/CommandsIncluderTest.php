<?php

namespace Psc\System\Console;

/**
 * @group class:Psc\System\Console\CommandsIncluder
 */
class CommandsIncluderTest extends \Psc\Code\Test\Base {
  
  protected $commandsIncluder;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Console\CommandsIncluder';
    parent::setUp();
    //$this->commandsIncluder = new CommandsIncluder();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>