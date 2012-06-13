<?php

namespace Psc\CMS;

use Psc\CMS\ProjectWizard;
use Psc\PSC;

/**
 * @group class:Psc\CMS\ProjectWizard
 */
class ProjectWizardTest extends \Psc\Code\Test\Base {
  
  protected $factory;
  
  public function testCreate() {
    $factory = $this->getProjectsFactory();
    
    $projectWizard = new ProjectWizard('MyNewProject', $this->factory);
    $this->assertInstanceof('\Psc\CMS\ProjectWizard', $projectWizard);
    $this->assertEquals('MyNewProject', $projectWizard->getProjectName());
    
    return $projectWizard;
  }
  
  protected function getProjectsFactory() {
    $this->factory = clone PSC::getProjectsFactory();
    $this->factory->setProjectsRoot($this->getTestDirectory());
    return $this->factory;
  }

  /**
   * @depends testCreate
   */
  public function testGetDefaultRoot(ProjectWizard $projectWizard) {
    $root = $this->getProjectsFactory()->getProjectRoot('MyNewProject');
    
    $this->assertEquals((string) $root, (string) $projectWizard->getDefaultProjectRoot());
  }
  
  /**
   * @depends testCreate
   */
  public function testSetProjectRoot(ProjectWizard $projectWizard) {
    $projectWizard->setProjectRoot($r = $this->getTestDirectory('MyNewProject/Umsetzung/'));
    
    $this->assertEquals((string) $r, (string) $projectWizard->getProjectRoot());
    return $projectWizard;
  }
  
  /**
   * @depends testSetProjectRoot
   */
  public function testSetProjectClassPath(ProjectWizard $projectWizard) {
    $projectWizard->setProjectClassPath($r = $this->getTestDirectory('MyNewProject/Umsetzung/base/src/MyNewProject/'));
    
    $this->assertEquals((string) $r, (string) $projectWizard->getDefaultProjectClassPath());
    $this->assertEquals((string) $r, (string) $projectWizard->getProjectClassPath());
    return $projectWizard;
  }

  /**
   * @depends testSetProjectRoot
   */
  public function testEmptyRoot(ProjectWizard $projectWizard) {
    $root = $projectWizard->getProjectRoot();
    
    // files createn
    $f = $this->newFile('nichtleeredatei.txt', 'MyNewProject/Umsetzung/');
    $f->writeContents('blubb');
    $this->assertFileExists((string) $f);
    
    $this->assertFalse($root->isEmpty());
    
    $projectWizard->emptyRoot();
    
    $this->assertTrue($root->isEmpty());
    return $projectWizard;
  }
  
  /**
   * @depends testSetProjectClassPath
   */
  public function testInit(ProjectWizard $projectWizard) {
    $projectWizard->init();
    
    $root = $this->getTestDirectory('MyNewProject/Umsetzung/');
    $cp = $this->getTestDirectory('MyNewProject/Umsetzung/base/src/MyNewProject/');
    
    $project = $projectWizard->getProject();
    $this->assertEquals((string) $root, (string) $project->getRoot());
    $this->assertEquals((string) $cp, (string) $project->getClassPath());
    $this->assertInstanceOf('Psc\CMS\Configuration', $project->getHostConfig());
    $this->assertEquals('MyNewProject', (string) $project->getName());
    
    return $projectWizard;
  }
  
  /**
   * @depends testInit
   *
   * das ist quasi schon der fast letzte Test in unser unit kette, denn ab jetzt können wir jeden task individuell testen
   */
  public function testRunTask(ProjectWizard $projectWizard) {
    $task = new SampleTask('sample');
    
    $this->assertFalse($task->hasRun());
    $this->assertInstanceof('Psc\CMS\ProjectWizard', $projectWizard->registerTask($task));
    
    $projectWizard->runTask('sample');
    
    $this->assertTrue($task->hasRun());
  }
}

class SampleTask extends \Psc\CMS\ProjectWizardTask {
  
  public function __construct($name) {
    $this->setName($name);
  }
  
  public function run(ProjectWizard $projectWizard) {
    $this->hasRun = TRUE;
  }
}
?>