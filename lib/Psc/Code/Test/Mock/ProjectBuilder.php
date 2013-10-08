<?php

namespace Psc\Code\Test\Mock;

use Doctrine\ORM\EntityManager;
use Webforge\Common\System\Dir;
use Psc\CMS\Project;
use Psc\CMS\ProjectsFactory;
use Webforge\Configuration\Configuration;

class ProjectBuilder extends \Psc\Code\Test\Mock\Builder {
  
  protected $name;
  protected $root;
  protected $configuration;
  protected $mode;
  protected $paths;
  protected $projectsFactory;
  
  public function __construct(\Psc\Code\Test\Base $testCase, $name, Dir $root, Array $paths = NULL, $mode = Project::MODE_SRC) {
    $this->name = $name;
    $this->root = $root;
    $this->hostConfig = $testCase->getHostConfig();
    $this->projectsFactory = new ProjectsFactory($this->hostConfig);
    $this->paths = $paths;
    $this->mode = $mode;
    
    parent::__construct($testCase, $fqn = 'Psc\CMS\Project');
  }
  
  
  public function build($realMock = FALSE) {
    if (!isset($this->name)) {
      throw IncompleteBuildException::missingVariable('name');
    }

    if (!($this->root instanceof Dir)) {
      throw IncompleteBuildException::missingVariable('root');
    }

    if (!isset($this->paths)) {
      $this->paths = $this->projectsFactory->getProjectPaths($this->name, $this->mode);
    }
      
    if (!is_array($this->paths)) {
      throw IncompleteBuildException::missingVariable('paths');
    }
    
    if ($realMock) {
      return $this->buildMock(
        array(
          $this->name,
          $this->root,
          $this->hostConfig,
          $this->paths,
          $this->mode
        ),
        TRUE
      );
    } else {
      return new Project(
        $this->name,
        $this->root,
        $this->hostConfig,
        $this->paths,
        $this->mode
      );
    }
  }

  /**
   * @param name des Projektes (Projektkürzel)
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }
  
  
  public function setHostConfig(Configuration $hostConfig) {
    $this->hostConfig = $hostConfig;
    return $this;
  }

  /**
   * @param const Project::MODE_PHAR, Project::MODE_SRC
   */
  public function setMode($mode) {
    $this->mode = $mode;
    return $this;
  }  
}
?>