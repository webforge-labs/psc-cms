<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\ProjectConsole
 */
class ProjectConsoleTest extends \Psc\Code\Test\Base {
  
  protected $projectConsole;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\ProjectConsole';
    parent::setUp();
    $this->projectConsole = new ProjectConsole();
  }
  
  public function testDPICreater() {
    $this->assertInstanceOf('\Psc\System\Console\Console', $this->projectConsole);
  }
}
?>