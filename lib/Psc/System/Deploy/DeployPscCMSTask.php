<?php

namespace Psc\System\Deploy;

use Webforge\Common\System\Dir;
use Psc\CMS\Project;
use Psc\Code\Build\LibraryBuilder;
use Webforge\Framework\Container AS WebforgeContainer;
use Webforge\Framework\Package\Package;
use RuntimeException;

class DeployPscCMSTask extends \Psc\SimpleObject implements Task {

  protected $cliPHPTemplate = <<< 'PHP_CLI'
<?php

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'%autoPrependFile%';

$console = new %projectConsole%($project = \Psc\PSC::getProject(), $project->getModule('Doctrine'));
$console->run();

PHP_CLI;

  protected $autoPrependFile = 'auto.prepend.php';
  
  protected $libraryBuilder;
  
  /**
   * @var Webforge\Common\System\Dir
   */
  protected $targetProject;
  
  /**
   * @var Psc\CMS\Project
   */
  protected $psc;
  
  public function __construct(WebforgeContainer $webforgeContainer, Project $targetProject, LibraryBuilder $libraryBuilder = NULL) {
    $this->cliPHPTemplate .= '?>'; // stupid sublime
    $packageRegistry = $webforgeContainer->getPackageRegistry();
    $bridge = $webforgeContainer->getCMSBridge();
    
    $this->psc = $bridge->createProjectFromPackage($packageRegistry->findByIdentifier('pscheit/psc-cms'));
    $bridge->initLocalConfigurationFor($this->psc);

    $vendor = $webforgeContainer->getLocalPackage()->getDirectory(Package::VENDOR);
    $packageIdentifier = 'pscheit/psc-cms-js';
    $this->pscjs = $vendor->sub($packageIdentifier.'/');
    
    if (!$this->pscjs->exists()) {
      throw new RuntimeException($webforgeContainer->getLocalPackage()->getIdentifier().' has not a dependency: '.$packageIdentifier.' installed. Add it to composer.json');
    }
    
    $this->libraryBuilder = $libraryBuilder ?:
                              new LibraryBuilder(
                                $this->psc
                              );
    $this->targetProject = $targetProject;
  }
  
  public function run() {
    $this->targetProject->getBin()->create();
    //$this->buildPscCMS();
    $this->buildCLI();
    //$this->jsHintPscCMSJS();
    $this->copyPscCMSJSAndPscCSS();
    $this->copyErrors();
  }
  
  protected function jsHintPscCMSJS() {
    system(sprintf('cd %s && grunt jshint', (string) $this->pscjs));
  }
  
  protected function copyPscCMSJSAndPscCSS() {
    // in der .htaccess haben wir keinen alias für /psc-cms und deshalb erstellen wir das verzeichnis hier und kopieren alles da hinein

    // kopieren für (was auch immer) htdocs ist
    $target = $this->targetProject->getHtdocs();
    //$this->psc->getHtdocs()->sub('js/')->copy($target->sub('js/')->create(), NULL, array('ui-dev','fixtures'), TRUE);
    $this->psc->getHtdocs()->sub('css/')->copy($target->sub('css/')->create(), NULL, NULL, TRUE);
    foreach (array('lib/','css/','img/','vendor/') as $sub) {
      $this->pscjs->sub($sub)->copy($target->sub('psc-cms-js/'.$sub)->create(), NULL, NULL, TRUE);
    }

    // für cms htdocs 
    $cmsHtdocs = $this->targetProject->getBase()->sub('htdocs-cms/');
    if ($cmsHtdocs->exists()) {
      $target = $cmsHtdocs;
      //$this->psc->getHtdocs()->sub('js/')->copy($target->sub('js/')->create(), NULL, array('ui-dev','fixtures'), TRUE);
      $this->psc->getHtdocs()->sub('css/')->copy($target->sub('css/')->create(), NULL, NULL, TRUE);
      
      foreach (array('lib/','css/','img/','vendor/') as $sub) {
        $this->pscjs->sub($sub)->copy($cmsHtdocs->sub('psc-cms-js/'.$sub)->create(), NULL, NULL, TRUE);
      }
    }
  }
  
  protected function copyErrors() {
    // kopieren für (was auch immer) htdocs ist
    $target = $this->targetProject->getHtdocs()->sub('errors/');
    if (!$target->exists()) {
      $this->psc->getHtdocs()->sub('errors/')->copy($target->create(), NULL, array('404.wurf1.html'), TRUE);
    }
  }
  
  protected function buildCLI() {
    $vars = array('project'=>$this->targetProject->getName(),
                  'projectConsole'=>'\\'.$this->targetProject->getNamespace().'\\ProjectConsole',
                  'autoPrependFile'=>$this->autoPrependFile
                  );

    $this->targetProject->getBin()
      ->getFile('cli.php')
        ->writeContents(\Psc\TPL\TPL::miniTemplate($this->cliPHPTemplate, $vars));
  }
  
  protected function buildPscCMS() {
    $src = $this->targetProject->getBin();
    $file = $src->getFile('psc-cms.phar.gz');
    $this->libraryBuilder->compile($file);
  }
  
  /**
   * @param string $autoPrependFile
   * @chainable
   */
  public function setAutoPrependFile($autoPrependFile) {
    $this->autoPrependFile = $autoPrependFile;
    return $this;
  }

  /**
   * @return string
   */
  public function getAutoPrependFile() {
    return $this->autoPrependFile;
  }
}
?>