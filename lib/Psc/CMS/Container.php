<?php

namespace Psc\CMS;

use Webforge\Framework\Container as WebforgeContainer;
use Webforge\Common\System\Dir;
use Webforge\Framework\LocalPackageInitException;
use Psc\PSC;

class Container {
  
  public $webforge;
  
  protected $rootDirectory;
  
  protected $project;
  
  public function __construct($rootDirectory) {
    $this->webforge = new WebforgeContainer();
    $this->initRootDirectory($rootDirectory);
  }
  
  public function init() {
    ini_set('mbstring.internal_encoding', 'UTF-8');
    $GLOBALS['env']['root'] = $this->rootDirectory;
    
    $this->initPSCStaticClass();
  }
  
  protected function initPSCStaticClass() {
    // some legacy static class from older projects
    PSC::setProject($this->getProject());
    
    // the projectsfactory has the same host config as we have
    PSC::setProjectsFactory($this->getProjectsFactory());
  }
  
  protected function initRootDirectory($rootDirectory) {
    if ($rootDirectory instanceof Dir) {
      $this->rootDirectory = $rootDirectory;
    } else {
      $this->rootDirectory = Dir::factoryTS($rootDirectory);
    }
  }
  
  public function getProject() {
    if (!isset($this->project)) {
      $this->initLocalWebforgePackage();
      $this->project = $this->webforge->getLocalProject();
      
      if (PSC::isTravis()) {
        $this->setInTests();
      }
    }
    
    return $this->project;
  }
  
  /**
   * Tries to init the package with webforge automatically
   *
   * this can fail in some cases:
   *   1. the easiest way is to webforge register-package the package which should be bootstrapped
   *   2. the next way would be to provide a composer.json with autoload infos in the rootDirectory of the container
   *   3. some older projects can have their composer.json in root\Umsetzung\base\src but this is discouraged to use and can be removed in the future
   *
   * after this is called the local package should be registered in webforge container (getLocalPackage() / getLocalProject())
   */
  protected function initLocalWebforgePackage() {
    try {
      $this->webforge->initLocalPackageFromDirectory($this->rootDirectory);
    } catch (LocalPackageInitException $e) {
      // this could happen for packages that are not (yet) registered by webforge
      // but thats not a problem at first hand: we assume that we can find a composer.json somewhere
      
      $this->webforge->getPackageRegistry()->addComposerPackageFromDirectory(
        $composerRoot = $this->findComposerFolder()
      );
      
      // try again to init (its faster to use $composerRoot here, allthough this->rootDirectory would do it anyway)
      $this->webforge->initLocalPackageFromDirectory($composerRoot); 
    }  
  }
  
  /**
   * Finds the composer folder for oldstyleprojects and normal projects (webforge projects)
   *
   * oldStyleProjects have a "Umsetzung" Folder in its root
   */
  protected function findComposerFolder() {
    $src = $this->rootDirectory->sub('Umsetzung/base/src/');
    
    if ($src->exists()) {
      return $src;
    } else {
      return $this->rootDirectory();
    }
  }
  
  /**
   * @return Psc\CMS\ProjectsFactory
   */
  public function getProjectsFactory() {
    return $this->webforge->getCMSBridge()->getProjectsFactory();
  }
  
  /**
   * @return Psc\CMS\Configuration
   */
  public function getHostConfig() {
    return $this->webforge->getCMSBridge()->getHostConfig();
  }
  
  public function setInTests() {
    $this->getProject()->setTests(TRUE);
    return $this;
  }
}
?>