<?php

namespace Psc\CMS;

use Psc\CMS\ProjectWizzardTask;

/**
 * @group class:Psc\CMS\ProjectWizardTask
 */
class ProjectWizardTaskTest extends \Psc\Code\Test\Base {

  public function testConstruction() {
    
    $task = new SampleTaskT('sample');
    
    $this->assertEquals('sample',$task->getName());
    $task->run(new ProjectWizard('NewProject'));
    
    $this->assertTrue(TRUE, $task->hasRun());
  }
}

class SampleTaskT extends \Psc\CMS\ProjectWizardTask {
  
  public function __construct($name) {
    $this->setName($name);
  }
  
  public function run(ProjectWizard $projectWizard) {
    $this->hasRun = TRUE;
  }
}
?>