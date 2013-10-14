<?php

namespace Psc\System\Deploy;

use Webforge\Framework\Project;
use Mockery as m;

/**
 * @group class:Psc\System\Deploy\ConfigureApacheTask
 */
class ConfigureApacheTaskTest extends \Psc\Code\Test\Base {
  
  protected $configureApacheTask;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Deploy\ConfigureApacheTask';
    parent::setUp();
    
    $this->testDir = $this->getTestDirectory('target/');
    $this->targetProject = $this->doublesManager->Project('tiptoi', $this->testDir)->build();

    $this->targetProject = m::mock('Webforge\Framework\Project');
    $this->targetProject->shouldReceive('dir')->with('etc')->andReturn($this->testDir->sub('etc'));

    $this->task = new ConfigureApacheTask($this->targetProject, 'pegasus', 'tiptoi.ps-webforge.com');
    
    $this->task
      ->setServerName('tiptoi.ps-webforge.com')
      ->setServerAlias(array('test.tiptoi.ps-webforge.com', 'tiptoi.ps-webforge.net'))
    ;
  }
  
  public function testAcceptance() {
    $this->task->run();
    
    $this->assertFileExists((string) ($conf = $this->testDir->sub('conf/')->getFile('pegasus.conf')));
    
    $cfg = $conf->getContents();
    
    $this->assertContains('ServerName tiptoi.ps-webforge.com', $cfg);
    $this->assertContains('ServerAlias test.tiptoi.ps-webforge.com tiptoi.ps-webforge.net', $cfg);
  }
}
