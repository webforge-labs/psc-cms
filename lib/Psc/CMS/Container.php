<?php

namespace Psc\CMS;

use Webforge\Framework\Container as WebforgeContainer;
use Webforge\Common\System\Dir;

class Container {
  
  public $webforge;
  
  protected $rootDirectory;
  
  protected $project;
  
  public function __construct($rootDirectory) {
    $this->webforge = new WebforgeContainer();
    
    $this->initRootDirectory($rootDirectory);
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
      $this->webforge->initLocalPackageFromDirectory($this->rootDirectory);
      $this->project = $this->webforge->getLocalProject();
    }
    
    return $this->project;
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