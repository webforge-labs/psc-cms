<?php

namespace Psc\System\Deploy;

use Webforge\Common\System\Dir;
use Webforge\Framework\Project;
use Psc\Code\Build\LibraryBuilder;
use Webforge\Framework\Container AS WebforgeContainer;
use Webforge\Framework\Package\Package;
use RuntimeException;

class DeployPscCMSTask extends \Psc\SimpleObject implements Task {


  protected $autoPrependFile = 'auto.prepend.php';
  
  protected $libraryBuilder;

  protected $useBuild = FALSE;
  
  /**
   * @var Webforge\Common\System\Dir
   */
  protected $targetProject;
  
  /**
   * @var Webforge\Framework\Project
   */
  protected $psc;
  
  public function __construct(WebforgeContainer $webforgeContainer, Project $targetProject, LibraryBuilder $libraryBuilder = NULL) {

    if ($targetProject->loadedFromPackage) {
      $this->autoPrependFile = 'bootstrap.php';
      $this->cliPHPTemplate = <<< 'PHP_CLI'
#!/usr/bin/env php      
<?php

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'%autoPrependFile%';

$console = new %projectConsole%($project = \Psc\PSC::getProject(), $project->getModule('Doctrine'));
$console->run();

PHP_CLI;
    } else {
      $this->cliPHPTemplate = <<< 'PHP_CLI'
#!/usr/bin/env php
<?php

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'%autoPrependFile%';

$console = new %projectConsole%($project = \Psc\PSC::getProject(), $project->getModule('Doctrine'));
$console->run();

PHP_CLI;
    }
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
    if ($this->useBuild) {
      system(sprintf('cd %s && grunt jshint', (string) $this->pscjs));
      system(sprintf('cd %s && grunt requirejs', (string) $this->pscjs));
    }
  }
  
  protected function copyPscCMSJSAndPscCSS() {
    // in der .htaccess haben wir keinen alias für /psc-cms und deshalb erstellen wir das verzeichnis hier und kopieren alles da hinein

    // kopieren für (was auch immer) htdocs ist
    $target = $this->targetProject->getHtdocs();
    $this->psc->getHtdocs()->sub('css/')->copy($target->sub('css/')->create(), NULL, NULL, TRUE);

    $pscjsSource = $this->pscjs;
    if ($this->useBuild) {
      $pscjsSource = $this->pscjs->sub('build/');
    }

    // psc-cms-js sources kopieren
    foreach (array('lib/','css/','img/','vendor/', 'templates/') as $sub) {
      $pscjsSource->sub($sub)
        ->copy($target->sub('psc-cms-js/'.$sub)->create(), NULL, NULL, TRUE);
    }

    // für cms htdocs 
    if ($this->targetProject->loadedFromPackage) {
      $cmsHtdocs = $this->targetProject->getBase()->sub('www/cms/');
    } else {
      $cmsHtdocs = $this->targetProject->getBase()->sub('htdocs-cms/');
    }
    
    if ($cmsHtdocs->exists()) {
      $target = $cmsHtdocs;
      $this->psc->getHtdocs()->sub('css/')->copy($target->sub('css/')->create(), NULL, NULL, TRUE);
      
      foreach (array('lib/','css/','img/','templates/','vendor/') as $sub) {
        $pscjsSource->sub($sub)->copy($cmsHtdocs->sub('psc-cms-js/'.$sub)->create(), NULL, NULL, TRUE);
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
                  'projectConsole'=>'\\'.$this->targetProject->getNamespace().($this->targetProject->loadedFromPackage ? '\\CMS' : '').'\\ProjectConsole',
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

  public function useBuildPscJs() {
    $this->useBuild = TRUE;
    return $this;
  }
}
