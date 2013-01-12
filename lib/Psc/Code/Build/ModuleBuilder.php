<?php

namespace Psc\Code\Build;

use Psc\CMS\Module;
use Webforge\Common\System\File;

/**
 *
 * @TODO Schöner wäre natürlich Interface PharCompilable statt ModuleBuilder und ProjectBuilder
 */
class ModuleBuilder extends \Psc\Object {
  
  protected $module;
  
  protected $phar;
  
  protected $snippets;
  
  public $log;

  protected $pharBootstrapCode = <<< BOOTSTRAP__
<?php

/*%%NAMESPACE_DEFINITION%%*/

/*%%AUTOLOAD%%*/

/*%%AUTOSTART%%*/
?>
BOOTSTRAP__;
  
  
  public function __construct(\Psc\CMS\Module $module) {
    $this->module = $module;
    
    $this->setUp();
  }
  
  public function setUp() {
    $this->generateSnippets();
  }
  
  public function buildPhar(File $out) {
    $this->phar = new \Psc\Code\Build\Phar($out,
                                           $this->module->getClassPath(),
                                           $this->module->getNamespace()
                                          );
    
    if (count($addFiles = $this->module->getAdditionalPharFiles()) > 0) {
      foreach ($addFiles as $url => $addFile) {
        $this->phar->addFile($addFile, File::createFromURL($url, NULL));
      }
    }

    if (count($addFiles = $this->module->getAdditionalClassFiles()) > 0) {
      foreach ($addFiles as $url => $addFile) {
        $this->phar->addClassFile($addFile);
      }
    }
    
    /* Start Snippet */
    $startphp = $this->module->getAutostartFile();
    if ($startphp instanceof \Webforge\Common\System\File && $startphp->exists()) {
      $this->snippets->set('AUTOSTART',$this->snippetRequire('/auto.start.php'));
      $this->phar->addFile($startphp, new File('.'.DIRECTORY_SEPARATOR.'auto.start.php'));
    }
    
    /* @TODO hier können wir module-dependencies checken (aber nicht selbst bauen) */
    
    $this->phar->setBootstrapCode($this->getPharBootstrapCode());
    
    $this->phar->build();
    return $this;
  }
  
  public function getPharBootstrapCode() {
    return $this->snippets->replace($this->pharBootstrapCode);
  }

  public function generateSnippets() {
    if (!isset($this->snippets)) {
      $this->snippets = new Snippets();
      
      $this->snippets->set('NAMESPACE_DEFINITION','namespace '.$this->module->getNamespace().';');
      
      $this->snippets->set('AUTOLOAD',<<< 'SNIPPET'
/* Klassen vom Modul zum Psc\PharAutoLoader hinzufügen */

/* Es kann sein, dass psc nativ geladen wurde, dann müssen wir uns einen eigenen PharAutoloader nehmen */
$phal = \Psc\PSC::getAutoLoader() ?: new \Psc\PharAutoloader();

$phal->addPaths(array_map(function ($path) {
                         return \Phar::running(TRUE).$path;
                       }, /*%%CLASS2PATH%%*/)
            );
SNIPPET
);
      $this->snippets->set('BOOTSTRAP',"\Psc\PSC::getProject->getModule('/*%%MODULE%%*/')->bootstrap();'");
      $this->snippets->set('MODULE',$this->module->getName());
    }
  }
  
  public function log($msg) {
    $this->log[] = $msg;
  }
}
?>