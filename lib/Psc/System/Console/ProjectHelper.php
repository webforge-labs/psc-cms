<?php

namespace Psc\System\Console;

class ProjectHelper extends \Symfony\Component\Console\Helper\Helper {
  
  protected $project;
  
  public function __construct(\Psc\CMS\Project $project) {
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