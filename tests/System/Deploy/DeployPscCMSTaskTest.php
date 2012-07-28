<?php

namespace Psc\System\Deploy;

/**
 * @group class:Psc\System\Deploy\DeployPscCMSTask
 */
class DeployPscCMSTaskTest extends \Psc\Code\Test\Base {
  
  protected $task;
  protected $target;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Deploy\DeployPscCMS';
    parent::setUp();
    
    $this->targetProject = $this->doublesManager->Project('testProject', $this->target = $this->getTestDirectory('target/'))->build();
    
    $this->task = new DeployPscCMSTask($this->targetProject);
  }
  
  public function testAcceptance() {
    $this->markTestSkipped('it goes on my nerves');
    $this->task->run();
    
    $this->assertFileExists((string) $this->target->sub('base/src/')->getFile('psc-cms.phar.gz'));
  }
  
  public function tearDown() {
    $this->target = $this->getTestDirectory('target/')->wipe();
  }
}
?>