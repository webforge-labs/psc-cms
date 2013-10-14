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
class CallbackTask extends AbstractTask {
  
  protected $targetProject;
  protected $webforge;

  protected $onRun;
  
  public function __construct(Project $targetProject) {
    $this->targetProject = $targetProject;
    parent::__construct('Callback');
  }

  public function onRun($callback) {
    $this->onRun = $callback;
    return $this;
  }
  
  public function run() {
    $cb = $this->onRun;

    return call_user_func($cb, $this->targetProject);
  }
}
