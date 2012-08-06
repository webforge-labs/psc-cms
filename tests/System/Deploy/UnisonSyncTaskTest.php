<?php

namespace Psc\System\Deploy;

/**
 * @group class:Psc\System\Deploy\UnisonSyncTask
 */
class UnisonSyncTaskTest extends \Psc\Code\Test\Base {
  
  protected $unisonSyncTask;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Deploy\UnisonSyncTask';
    parent::setUp();
    
    $dir = new \Psc\System\Dir('D:\www\deployments\tiptoi.staging\\');
    $this->unisonSyncTask = new UnisonSyncTask($dir);
  }
  
  public function testAcceptance() {
    $this->markTestSkipped('this is not a real test');
    $profile = 'automatic.tiptoi.staging.ps-webforge.com@pegasus.ps-webforge.com';
    $this->unisonSyncTask->setProfile($profile);
    $this->unisonSyncTask->run();
  }
}
?>