<?php

namespace Psc\System\Deploy;

use Psc\CMS\Project;

class DeployDoctrineTask extends \Psc\SimpleObject implements Task {
  
  protected $targetProject;
  
  public function __construct(Project $targetProject) {
    $this->targetProject = $targetProject;
  }
  
  public function run() {
    // proxies verzeichnis erstellen, weil doctrine das nicht selbst macht
    $this->targetProject->getSrc()->sub('Proxies')->create();
    
  }
}
?>