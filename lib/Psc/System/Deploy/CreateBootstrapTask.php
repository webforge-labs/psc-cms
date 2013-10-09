<?php

namespace Psc\System\Deploy;

use Webforge\Framework\Project;

/*
auto.prepend.php:
<?
 
namespace Psc;

require __DIR__.'psc-cms.phar.gz';

// damit die module sich an den pharautoloader hängen
PSC::getProjectsFactory()->getProject('psc-cms')->bootstrap()
  ->getModule('Doctrine')->bootstrap()->getProject()
  ->getModule('Imagine')->bootstrap()->getProject()
;

*/

class CreateBootstrapTask implements Task {
  
  protected $targetProject;
  protected $modules;
  protected $baseUrl;
  protected $composerAutoLoading;
  
  public function __construct(Project $targetProject, $baseUrl, Array $modules = array()) {
    $this->targetProject = $targetProject;
    $this->modules = $modules;
    $this->baseUrl = $baseUrl;
  }
  
  public function run() {
    $this->createAutoPrepend();
    $this->stagingHack();
  }

  protected function stagingHack() {
    if ($this->targetProject->isStaging()) {
      $this->targetProject->getRoot()->getFile('staging')->writeContents('1');
    }
  }
  
  protected function createAutoPrepend() {
    if ($this->targetProject->loadedFromPackage) {
      $this->targetProject->getRoot()->getFile('bootstrap.php')->writeContents($this->buildAutoPrepend());
    } else {
      $src = $this->targetProject->getSrc()->create();
      $src->getFile('auto.prepend.php')->writeContents($this->buildAutoPrepend());
    }
  }
  
  /**
   * @return string
   */
  protected function buildAutoPrepend() {
    $template = <<<'PHP'
<?php

namespace Psc;
use Psc\Boot\BootLoader;

require 'package.boot.php';
$bootLoader = new BootLoader(__DIR__);
$bootLoader->loadComposer();
$bootLoader->registerCMSContainer();

PSC::getProject()
  ->setStaging(%staging%)
  ->bootstrap()
%modules%
  ->getConfiguration()->set(array('url','base'), '%baseUrl%');
;

?>
PHP;
    
    $vars = array(
      'staging'=>$this->targetProject->isStaging() ? 'TRUE' : 'FALSE',
      'projectMode'=>'\Psc\CMS\Project::MODE_SRC',
      'projectName'=>$this->targetProject->getName(),
      'baseUrl'=>$this->baseUrl,
      'composer'=>$this->composerAutoLoading ? "require_once \$bootLoader->getPath('../src/vendor/', BootLoader::RELATIVE | BootLoader::VALIDATE).'autoload.php';\n" : NULL
    );
    $vars['modules'] = $vars['phars'] = NULL;
    foreach ($this->modules as $moduleName) {
      $vars['modules'] .= sprintf("    ->getModule('%s')->bootstrap()->getProject()\n", $moduleName);
      
      if (!$this->composerAutoLoading) {
        $vars['phars'] .= sprintf('$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar(\'%s\'));'."\n", mb_strtolower($moduleName));
      }
    }

    
    return \Psc\TPL\TPL::miniTemplate($template, $vars);
  }
  
  /**
   * @param array $modules
   * @chainable
   */
  public function setModules(Array $modules) {
    $this->modules = $modules;
    return $this;
  }

  /**
   * @return array
   */
  public function getModules() {
    return $this->modules;
  }

  public function addModule($moduleName) {
    if (!in_array($moduleName, $this->modules)) {
      $this->modules[] = $moduleName;
    }
    return $this;
  }
  
  /**
   * @param boolean $composerAutoLoading
   * @chainable
   */
  public function setComposerAutoLoading($composerAutoLoading) {
    $this->composerAutoLoading = $composerAutoLoading;
    return $this;
  }

  /**
   * @return boolean
   */
  public function getComposerAutoLoading() {
    return $this->composerAutoLoading;
  }


}
?>