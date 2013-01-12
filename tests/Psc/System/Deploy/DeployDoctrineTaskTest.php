<?php

namespace Psc\System\Deploy;

/**
 * @group class:Psc\System\Deploy\DeployDoctrineTask
 */
class DeployDoctrineTaskTest extends \Psc\Code\Test\Base {
  
  protected $deployDoctrineTask;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Deploy\DeployDoctrineTask';
    parent::setUp();
    //$this->deployDoctrineTask = new DeployDoctrineTask();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>