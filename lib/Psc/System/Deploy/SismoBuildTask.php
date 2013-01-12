<?php

namespace Psc\System\Deploy;

use Webforge\Common\System\Dir;
use Psc\CMS\Project;

/**
 * 
 */
class SismoBuildTask extends \Psc\SimpleObject implements Task {
  
  /**
   * @var Psc\CMS\Project
   */
  protected $targetProject;
  
  public function __construct(Project $targetProject) {
    $this->targetProject = $targetProject;
    $psc = \Psc\PSC::getProjectsFactory()->getProject('psc-cms');
    $this->sismo = 'php '.$psc->getHtdocs()->sub('sismo')->getFile('index.php');
  }
  
  public function run() {
    return system(
      \Psc\TPL\TPL::miniTemplate('%sismo% --force --verbose build %project% ', //`git log -1 HEAD --pretty="%H"`
        array('sismo'=>$this->sismo,
              'project'=>$this->targetProject->getName()
             )
      )
    );
  }
}
?>