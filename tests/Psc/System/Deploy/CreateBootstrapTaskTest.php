<?php

namespace Psc\System\Deploy;

/**
 * @group class:Psc\System\Deploy\CreateBootstrapTask
 */
class CreateBootstrapTaskTest extends \Psc\Code\Test\Base {
  
  protected $task, $testDir, $targetProject;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Deploy\CreateBootstrapTask';
    parent::setUp();
    $this->testDir = $this->getTestDirectory('target/');
    
    $this->targetProject = $this->doublesManager->Project('test-project', $this->testDir)->build();
    
    $this->task = new CreateBootstrapTask($this->targetProject,
                                          'testproject.ps-webforge.com',
                                          $modules = array('Doctrine','PHPExcel', 'Imagine')
                                         );
  }
  
  public function testAcceptance() {
    $this->task->run();
    
    $this->assertFileExists((string) ($autoPrepend = $this->testDir->getFile('auto.prepend.php')));
    
    $contents = $autoPrepend->getContents();
    $this->assertContains("getProject('test-project')->bootstrap()", $contents);
    $this->assertContains("('Doctrine')->bootstrap()", $contents);
    $this->assertContains("('PHPExcel')->bootstrap()", $contents);
    $this->assertContains("('Imagine')->bootstrap()", $contents);
  }
}
