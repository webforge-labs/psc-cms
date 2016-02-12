<?php

namespace Psc\CMS;

use Webforge\Framework\Container as WebforgeContainer;
use Psc\CMS\ProjectsFactory;
use Webforge\Common\System\Dir;
use Psc\PSC;

class Container extends \Webforge\Setup\BootContainer {

  protected $modules;
  
  protected $inTests = NULL;
  
  protected $projetsFactory;
  
  public function init() {
    $GLOBALS['env']['root'] = $this->rootDirectory;
    
    $this->initPSCStaticClass();

    if (PSC::isTravis()) {
      $this->setInTests(TRUE);
    }
  }
  
  protected function initPSCStaticClass() {
    // some legacy static class from older projects
    PSC::setProject($this->getProject());
    
    // the projectsfactory has the same host config as we have
    PSC::setProjectsFactory($this->getProjectsFactory());
  }

  public function initErrorHandlers() {
    /* include path setzen */
    //PSC::getEnvironment()->addIncludePath((string) $this->getSrc(),'prepend'); // checkt ob include path schon gesetzt ist 
    
    PSC::registerExceptionHandler();
    PSC::registerErrorHandler();
    PSC::registerFatalErrorHandler();
  }

  /**
   * @return Psc\CMS\ProjectsFactory
   */
  public function getProjectsFactory() {
    if (!isset($this->projectsFactory)) {
      $this->projectsFactory = new ProjectsFactory($this->webforge->getHostConfiguration());
    }
    
    return $this->projectsFactory;
  }
  
  protected function getModules() {
    if (!isset($this->modules)) {
      $this->modules = new Modules($this->getProject(), $this->inTests());
    }

    return $this->modules;
  }

  public function getModule($name) {
    return $this->getModules()->get($name);
  }

  public function bootstrapModule($name) {
    return $this->getModules()->bootstrap($name);
  }

  public function bootstrapModuleIfExists($name) {
    return $this->getModules()->bootstrapIfExists($name);
  }
  
  public function setInTests($bool = TRUE) {
    $this->inTests = $bool;
    return $this;
  }

  /**
   * Indicates if the unit/acceptance tests are run
   * 
   * this is NOT a flag to circumvent tests or behaviour. this is a flag to use the test-datbase for unit-tests
   * @return bool
   */
  public function inTests() {
    if (!isset($this->inTests)) {
      $this->inTests = PSC::isTravis() || PSC::isPHPUnit();
    }

    return $this->inTests;
  }
}
