<?php

namespace Psc\System\Deploy;

use Psc\CMS\Project;
use Webforge\Common\System\File;
use Psc\String AS S;

use Webforge\Setup\Installer\CopyCmd;

class CopyTask extends \Psc\SimpleObject implements Task {
  
  protected $sourceProject, $targetProject;
  
  protected $copyCommands = array();
  
  public function __construct(Project $sourceProject, Project $targetProject, Array $ignoreSourceDirectories = array()) {
    $this->sourceProject = $sourceProject;
    $this->targetProject = $targetProject;
  }
  
  public function run() {
    foreach ($this->copyCommands as $copy) {
      $copy->execute();
    }
  }
  
  public function copy($source, $destination = NULL) {
    list ($source, $destination) = $this->expandSourceAndDestination($source, $destination);
    
    $cmd = new CopyCmd($source, $destination);
    $this->copyCommands[] = $cmd;
    
    return $this;
  }
  
  protected function expandSourceAndDestination($source, $destination = NULL) {
    if (is_string($source)) {
      $sourceUrl = $source;
      
      if (S::endsWith($source, '/')) {
        $source = $this->sourceProject->getRoot()->sub($source);
      } else {
        $source = File::createFromURL($source, $this->sourceProject->getRoot());
      }
    }
    
    if (!isset($destination)) {
      if (!isset($sourceUrl)) {
        $sourceUrl = $source->getUrl($this->sourceProject->getRoot());
        if ($source instanceof Dir) {
          $sourceUrl .= '/';
        }
      }
      
      $destination = $sourceUrl;
    }
    
    if (is_string($destination)) {
      if (S::endsWith($destination, '/')) {
        $destination = $this->targetProject->getRoot()->sub($destination);
      } else {
        $destination = File::createFromURL($destination, $this->targetProject->getRoot());  
      }
    }
    
    return array ($source, $destination);
  }
  
  public function copyDirs($source, Array $dirs, $destination = NULL) {
    list ($source, $destination) = $this->expandSourceAndDestination($source, $destination);
    
    foreach ($dirs as $sub) {
      $cmd = new CopyCmd($source->sub($sub), $destination->sub($sub));
      $this->copyCommands[] = $cmd;
    }

    return $this;
  }
}
?>