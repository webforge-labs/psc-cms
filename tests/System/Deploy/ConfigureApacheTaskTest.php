<?php

namespace Psc\System\Deploy;

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
    $this->task = new ConfigureApacheTask($this->targetProject, 'pegasus');
    
    $this->task
      ->setServerName('tiptoi.ps-webforge.com')
      ->setServerAlias(array('test.tiptoi.ps-webforge.com', 'tiptoi.ps-webforge.net'))
    ;
  }
  
  public function testAcceptance() {
    $this->task->run();
    
    $this->assertFileExists((string) ($conf = $this->testDir->sub('conf/')->getFile('pegasus.conf')));
    
    $cfg = $conf->getContents();
    
    print $cfg;
    
    $this->assertContains('ServerName tiptoi.ps-webforge.com', $cfg);
    $this->assertContains('ServerAlias test.tiptoi.ps-webforge.com tiptoi.ps-webforge.net', $cfg);
  }
}
?>