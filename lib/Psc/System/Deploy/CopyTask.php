<?php

namespace Psc\System\Deploy;

use Webforge\Framework\Project;
use Webforge\Common\System\File;
use Webforge\Common\String AS S;

use Webforge\Setup\Installer\CopyCmd;

class CopyTask extends AbstractTask {

  protected $sourceProject, $targetProject;
  
  protected $copyCommands = array();

  protected $label;
  
  public function __construct(Project $sourceProject, Project $targetProject, Array $ignoreSourceDirectories = array()) {
    $this->sourceProject = $sourceProject;
    $this->targetProject = $targetProject;
    parent::__construct('Copy');
  }
  
  public function run() {
    $output = new \Webforge\Console\StringCommandOutput();
    foreach ($this->copyCommands as $copy) {
      $copy->execute($output);
    }
  }

  
  public function copy($source, $destination = NULL) {
    list ($source, $destination) = $this->expandSourceAndDestination($source, $destination);
    
    $cmd = new CopyCmd($source, $destination, CopyCmd::CREATE_DESTINATION);
    $this->copyCommands[] = $cmd;
    
    return $this;
  }
  
  protected function expandSourceAndDestination($source, $destination = NULL) {
    if (is_string($source)) {
      $sourceUrl = $source;
      
      if (S::endsWith($source, '/')) {
        $source = $this->sourceProject->getRootDirectory()->sub($source);
      } else {
        $source = File::createFromURL($source, $this->sourceProject->getRootDirectory());
      }
    }
    
    if (!isset($destination)) {
      if (!isset($sourceUrl)) {
        $sourceUrl = $source->getUrl($this->sourceProject->getRootDirectory());
        if ($source instanceof Dir) {
          $sourceUrl .= '/';
        }
      }
      
      $destination = $sourceUrl;
    }
    
    if (is_string($destination)) {
      if (S::endsWith($destination, '/')) {
        $destination = $this->targetProject->getRootDirectory()->sub($destination);
      } else {
        $destination = File::createFromURL($destination, $this->targetProject->getRootDirectory());  
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
