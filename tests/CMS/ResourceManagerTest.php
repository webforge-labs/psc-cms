<?php

namespace Psc\CMS;

use Psc\CMS\ResourceManager;
use Psc\PSC;

/**
 * @group class:Psc\CMS\ResourceManager
 */
class ResourceManagerTest extends \Psc\Code\Test\Base {
  
  public function testDependencyInjection() {
    $resourceManager = new ResourceManager();
    $this->assertInstanceOf('Psc\CMS\Project',$resourceManager->getProject());
    
    $resourceManager = new ResourceManager(PSC::getProject());
    $this->assertInstanceof('Psc\CMS\Project',$resourceManager->getProject());
  }
  
  public function testGetCMSResources() {
    $resourceManager = new ResourceManager(PSC::getProjectsFactory()->getProject('psc-cms'));
    
    $this->assertInstanceOf('Psc\System\File', $jsFile = $resourceManager->getFile(array('js','cms','ui.comboBox.js')));
    $this->assertGreaterThan(0, $jsFile->getSize());

    $this->assertInstanceOf('Psc\System\File', $cssFile = $resourceManager->getFile(array('css','reset.css')));
    $this->assertGreaterThan(0, $cssFile->getSize());
  }
  
  protected function createResourceManager() {
    return new ResourceManager();
  }
}
?>