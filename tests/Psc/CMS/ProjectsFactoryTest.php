<?php

namespace Psc\CMS;

use \Psc\PSC;

/**
 * @group class:Psc\CMS\ProjectsFactory
 */
class ProjectsFactoryTest extends \PHPUnit_Framework_TestCase {
  
  public function testSetGetProjectsPath() {
    $projectsFactory = new ProjectsFactory(new Configuration(array()));

    $paths = $projectsFactory->getProjectPaths('accept');
    $this->assertArrayHasKey(PSC::PATH_SRC, $paths);
    $this->assertArrayHasKey(PSC::PATH_BIN, $paths);
    $this->assertArrayHasKey(PSC::PATH_FILES, $paths);
    
    $projectsFactory->setProjectPath('accept',PSC::PATH_SRC, './my/custom/path');
    $paths = $projectsFactory->getProjectPaths('accept');
    $this->assertEquals('./my/custom/path',$paths[PSC::PATH_SRC]);
    
    $projectsFactory->setProjectPath('accept','src', './my/custom/path2');
    $paths = $projectsFactory->getProjectPaths('accept');
    $this->assertEquals('./my/custom/path2',$paths[PSC::PATH_SRC]);
  }
}
?>