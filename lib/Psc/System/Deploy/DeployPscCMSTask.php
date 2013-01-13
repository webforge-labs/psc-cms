<?php

namespace Psc\System\Deploy;

use Webforge\Common\System\Dir;
use Psc\CMS\Project;
use Psc\Code\Build\LibraryBuilder;
use Webforge\Framework\Container AS WebforgeContainer;

class DeployPscCMSTask extends \Psc\SimpleObject implements Task {

  protected $cliPHPTemplate = <<< 'PHP_CLI'
<?php

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'%autoPrependFile%';

$console = new %projectConsole%($project = \Psc\PSC::getProject(), $project->getModule('Doctrine'));
$console->run();

?>
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
    $packageRegistry = $webforgeContainer->getPackageRegistry();
    $bridge = $webforgeContainer->getCMSBridge();
    
    $this->psc = $bridge->createProjectFromPackage($packageRegistry->findByIdentifier('pscheit/psc-cms'));
    $bridge->initLocalConfigurationFor($this->psc);

    $this->pscjs = $packageRegistry->findByIdentifier('pscheit/psc-cms-js')->getRootDirectory();
    
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
    $this->jsHintPscCMSJS();
    $this->copyPscCMSJSAndPscCSS();
    $this->copyErrors();
    $this->copyPharsToBin();
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
  
  protected function copyPharsToBin() {
    $pscBin = \Psc\PSC::getRoot();
    
    // wir haben ja eigentlich alle per composer mittlerweile
    // das ist aber bissl unnütz hier, weil man meistens eh alles noch in bin/ im projekt hat (check this)
    $ignores = array('psc-cms.phar.gz', 'swift.phar.gz', 'hitch.phar.gz',
                     'doctrine.phar.gz', 'symfony.phar.gz',
                     'imagine.phar.gz','phpexcel.phar.gz',
                     'phppowerpoint.phar.gz');
    $pscBin->copy($this->targetProject->getBin(), '.gz', $ignores);
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