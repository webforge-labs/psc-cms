<?php

namespace Psc\System\Deploy;

use Webforge\Framework\Project;

/*
bootstrap.php (in root)
<?php
$ds = DIRECTORY_SEPARATOR;

// autoload project dependencies and self autoloading for the library
require_once __DIR__.$ds.'vendor'.$ds.'autoload.php';

if (file_exists($cmsBootstrap = getenv('PSC_CMS').'bootstrap.php')) {
  require_once $cmsBootstrap;
}

if (!class_exists('Psc\PSC', FALSE)) {
  require_once __DIR__.$ds.'vendor'.$ds.'pscheit'.$ds.'psc-cms'.$ds.'bin'.$ds.'psc-cms.phar.gz';
}

return $GLOBALS['env']['root'] = new \Webforge\Common\System\Dir(__DIR__.DIRECTORY_SEPARATOR);
?>
*/

class CreatePackageBootstrapTask implements Task {
  
  protected $targetProject;
  
  public function __construct(Project $targetProject) {
    $this->targetProject = $targetProject;
  }
  
  public function run() {
    $this->createBootstrapPHP();
  }
  
  protected function createBootstrapPHP() {
    $root = $this->targetProject->getRoot();

    $root->getFile('bootstrap.php')->writeContents($this->buildBootstrap());
  }
  
  /**
   * @return string
   */
  protected function buildBootstrap() {
    $template = <<<'PHP'
<?php
/**
 * Bootstrap and Autoload whole application
 *
 * you can use this file to bootstrap for tests or bootstrap for scripts / others
 */
$ds = DIRECTORY_SEPARATOR;

// autoload project dependencies and self autoloading for the library
require_once __DIR__.$ds.'vendor'.$ds.'autoload.php';

// autoload psc-cms
require_once __DIR__.$ds.'vendor'.$ds.'pscheit'.$ds.'psc-cms'.$ds.'bin'.$ds.'psc-cms.phar.gz';

return $GLOBALS['env']['root'] = new \Webforge\Common\System\Dir(__DIR__.DIRECTORY_SEPARATOR);
?>
PHP;
    
    return \Psc\TPL\TPL::miniTemplate($template, array());
  }
}
?>