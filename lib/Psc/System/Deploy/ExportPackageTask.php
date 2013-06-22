<?php

namespace Psc\System\Deploy;

use Psc\CMS\Project;
use Webforge\Common\System\File;
use Webforge\Common\String AS S;
use Webforge\Framework\Container;
use Psc\System\Console\Process;
use Psc\System\System;

/**
 * Exports a package which is installed in vendor with git archive
 */
class ExportPackageTask extends \Psc\SimpleObject implements Task {
  
  protected $targetProject;
  
  protected $destination;

  protected $package;

  protected $webforge;

  protected $branch = 'master';
  
  public function __construct(Project $targetProject, Container $webforgeContainer) {
    $this->targetProject = $targetProject;
    $this->webforge = $webforgeContainer;
  }
  
  public function run() {
    $dir = $this->package->getRootDirectory();
    $gitArchive = sprintf('git archive --format=tar %s | tar xvf - --directory %s', $this->branch, System::forceUnixPath($this->destination));
    
    $process = new Process($gitArchive, $dir, $envs = array());
    $process->setTimeout(0);
    
    $log = NULL;
    $ret = $process->run(function ($type, $buffer) use (&$log) {
      $log .= $buffer;
      
      //if ('err' === $type) {
      //    echo '[unison-ERR]: '.$buffer;
      //} else {
      //    echo '[unison-OUT]: '.$buffer;
      //}
    });

    if ($ret !== 0) {
      throw new \RuntimeException(sprintf("Cannot git archive the package.\nPackage: %s\nCmd: %s\nLog: %s\n", $this->package, $gitArchive, $log));
    }
  }

  public function setVendorPackage($identifier) {
    $this->package = $this->webforge->getVendorPackage($identifier);
    return $this;
  }

  public function setTarget($destination) {
    $this->destination = $this->expandDestination($destination);
    return $this;
  }
  
  protected function expandDestination($destination) {
    if (is_string($destination)) {
      if (S::endsWith($destination, '/')) {
        $destination = $this->targetProject->getRoot()->sub($destination);
      } else {
        $destination = File::createFromURL($destination, $this->targetProject->getRoot());  
      }
    }
    
    return $destination;
  }
}
