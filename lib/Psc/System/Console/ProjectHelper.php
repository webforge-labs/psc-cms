<?php

namespace Psc\System\Console;

use Webforge\Framework\Project;

class ProjectHelper extends \Symfony\Component\Console\Helper\Helper {
  
  protected $project;
  
  public function __construct(Project $project) {
    $this->project = $project;
  }
  
  public function getProject() {
    return $this->project;
  }

  /**
   * @see Helper
   */
  public function getName() {
    return 'project';
  }
}
?>