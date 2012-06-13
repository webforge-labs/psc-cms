<?php

namespace Psc\System\Deploy;

/**
 * @group class:Psc\System\Deploy\CopyProjectSources
 */
class CopyProjectSourcesTaskTest extends \Psc\Code\Test\Base {
  
  protected $task, $targetProject, $testDir;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Deploy\CopyProjectSources';
    parent::setUp();
    $this->testDir = $this->getTestDirectory('target/')->create()->wipe();
    
    $this->sourceDir = $this->getTestDirectory('source/');
    
    $this->sourceProject = $this->doublesManager->Project('test-project', $this->sourceDir)->build();
    $this->targetProject = $this->doublesManager->Project('test-project', $this->testDir)->build();
    
    $this->task = new CopyProjectSources($this->sourceProject, $this->targetProject);
  }
  
  public function testAcceptance() {
    $this->task->run();
    
    $this->assertFileExists((string) $this->testDir->getFile('base/src/bootstrap.php'));
    $this->assertFileExists((string) $this->testDir->sub('base/src/testProject')->getFile('Class1.php'));
    $this->assertFileExists((string) $this->testDir->sub('base/src/testProject')->getFile('Class2.php'));
    
    $this->assertFileExists((string) $this->testDir->sub('base/bin/')->getFile('_cli.bat'));
    $this->assertFileExists((string) $this->testDir->sub('base/bin/')->getFile('phpunit.xml'));
    $this->assertFileExists((string) $this->testDir->sub('base/src/tpl/')->getFile('test.html'));
  }
}
?>