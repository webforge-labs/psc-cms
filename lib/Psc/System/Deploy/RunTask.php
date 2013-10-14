<?php

namespace Psc\System\Deploy;

use Webforge\Framework\Project;
use Webforge\Common\System\File;
use Webforge\Common\System\Dir;
use Webforge\Framework\Container;
use Psc\System\Console\Process;
use Psc\System\System;

/**
 */
class RunTask extends AbstractTask {
  
  protected $targetProject;
  protected $webforge;

  protected $onRun;
  
  public function __construct(Project $targetProject, Container $webforgeContainer) {
    $this->targetProject = $targetProject;
    $this->webforge = $webforgeContainer;
    parent::__construct('Run');
  }

  public function onRun(\Closure $do) {
    $this->onRun = $do;
    return $this;
  }
  
  public function run() {
    $onRun = $this->onRun;

    $createProcess = function($cmd, $cwd = NULL, Array $envs = array()) {
      $process = new Process($cmd, $cwd, $envs);
      $process->setTimeout(0);

      return $process;
    };

    $onRun($this->targetProject, $createProcess);

  }
}
