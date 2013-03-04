<?php

namespace Psc\Code\Build;

use Psc\CMS\Project;
use Webforge\Common\System\Dir;
use Webforge\Common\System\File;
use Psc\PSC;
use Psc\Code\Code;

class ProjectBuilder extends \Psc\Object {
  
  protected $project;
  
  protected $buildName;
  
  protected $dependencies;
  
  protected $phar;
  
  protected $pharBootstrapCode;
  
  protected $snippets= NULL;
  
  public function __construct(Project $project, $buildName = 'default') {
    $this->project = $project;
    $this->buildName = $buildName;
    $this->setUp();
  }
  
  protected function setUp() {
    $this->generateBootstrapCode();
    $this->generateSnippets();
  }
    
  /**
   *
   *
   * - Wir nehmen alle Dateien vom Classpath des Projektes und Erstellen daraus ein Phar.
   *
   * ***** Dsa geht leider noch nicht: Experiments in process!
   * - wir fügen alle weiteren Libraries ebenfalls als PHARs hinzu (z. B. das psc-cms phar),
   *    denn darin ist der PharAutoloader, der dann ALLE Klassen (auch die es Projektes) lädt.
   *    Das ist ziemlich cool, weil wir einfach nur beim Bootstrapping das $class2path Construct
   *    an die psc-cms bootstrap weitergeben müssen
   *
   * deshalb alternative:
   *   alle anderen Libraries mit Abhängigkeiten werden relativ zur Position des PHARs angegeben und dann extern includiert
   *   (dann kann man später mal einen installer bauen mit einem großen phar welches dann die libs und bins entpackt)
   *   Umsetzung\base\build\$buildName\dependencies.php
   *
   * @see das build-phar von psc-cms ist hardcoded in build-phar-command
   * @param bool check ist dies true soll intelligent entschieden werden, ob das phar erstellt werden muss (fastcheck)
   */
  public function buildPhar(Dir $out, $check = FALSE) {
    $doCompile = TRUE;
    /* @TODO check? */
    
    if ($doCompile) {
      $this->phar = new \Psc\Code\Build\Phar(new File($out, $this->project->getLowerName().'.phar.gz'),
                                       $this->project->getClassPath(),
                                       $this->project->getNamespace());
      
      foreach ($this->getBuildDependencies() as $dependency) {
        /* copy phar-libs aber nicht ins phar sondern in das out-Directory */
        $target = $out->getFile($dependency->relFile);
        $target->getDirectory()->make(Dir::PARENT | DIR::ASSERT_EXISTS);
        $dependency->file->copy($target);
        print "  Adding Dependency[".$dependency->type."] to: ".$dependency->name.' ['.$dependency->relFile->getURL().']'."\n";
      }
      
      /* Start Snippet */
      $startphp = $this->project->getBuildPath($this->buildName)->getFile('auto.start.php');
      if ($startphp->exists()) {
        $this->snippets->set('AUTOSTART',$this->snippetRequire('/auto.start.php'));
        $this->phar->addFile($startphp, new File('.'.DIRECTORY_SEPARATOR.'auto.start.php'));
      }

      
      $this->phar->setBootstrapCode($this->getPharBootstrapCode());
      
      $this->phar->build();
      print \Webforge\Common\ArrayUtil::join($this->phar->getLog(),"    [phar] %s\n");
    }
  }
  
  protected function generateBootstrapCode() {
    $this->pharBootstrapCode = <<< BOOTSTRAP__
<?php

/*%%NAMESPACE_DEFINITION%%*/

/*%%PHAR_ROOT_DEFINITION%%*/

/*%%DEPENDENCIES%%*/

/*%%AUTOLOAD%%*/

/*%%BOOTSTRAP_PSC_CMS%%*/

/*%%AUTOSTART%%*/
?>
BOOTSTRAP__;
  }
  protected function generateSnippets($force = FALSE) {
    if (!isset($this->snippets) || $force) {
      $this->snippets = new Snippets();
      $this->snippets->set('NAMESPACE_DEFINITION','namespace '.$this->project->getNamespace().';');
      
      $this->snippets->set('PHAR_ROOT_DEFINITION', 'define("PHAR_ROOT", str_replace("/",DIRECTORY_SEPARATOR,dirname(\Phar::running(FALSE))).DIRECTORY_SEPARATOR);'); // str_replace da dirname phar:running(false) forward slashes hat
    
      $this->snippets->set('BOOTSTRAP_PSC_CMS',<<< 'SNIPPET__'
\Psc\PSC::getProjectsFactory()
  ->getProject('/*%%PROJECT%%*/', \Psc\CMS\Project::MODE_PHAR)
    ->setLoadedWithPhar(TRUE)
    ->setLibsPath(new \Webforge\Common\System\Dir(dirname(\Phar::running(FALSE)).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR))
    ->bootstrap();
SNIPPET__
);

      $this->snippets->set('AUTOLOAD', <<< 'SNIPPET__'
/* Klassen zum Psc\PharAutoLoader hinzufügen */
\Psc\PSC::getAutoLoader()->addPaths(/*%%CLASS2PATH%%*/);

SNIPPET__
);

      //$this->snippets['CLASS2PATH']  wird vom Build\Phar gesetzt!
      $this->snippets->set('PROJECT',$this->project->getName());
      
      /* DependencySnippet
      
        für jede Dependency fügen wir ein require in das snippet ein
        die dependencys bootstrappen sich dann selbst
        
        Reihenfolge muss man selbst durch die dependency-config sortieren!
      */
      $sn = NULL;
      $sn = "\$fileDir = dirname(\Phar::running(FALSE));\n\n";
      foreach ($this->getBuildDependencies() as $dependency) {
        $sn .= sprintf("/* boostrap: %s */\nrequire \$fileDir.DIRECTORY_SEPARATOR.'%s';\n",
                       $dependency->name,
                       (string) $dependency->relFile->getURL());
      }
      $this->snippets->set('DEPENDENCIES', $sn);
    }
  }
  
  /**
   * Gibt einen String zurück der ein Snippet ist, der eine File im Phar required
   */
  protected function snippetRequire($file) {
    return sprintf("require __DIR__.'%s';\n",$file);
  }
  
  protected function getPharBootstrapCode() {
    return $this->snippets->replace($this->pharBootstrapCode);
  }
  
  protected function getBuildDependencies() {
    if (!isset($this->dependencies)) {
      $this->dependencies = array();
      
      $dir = $this->project->getBuildPath($this->buildName);
      if (($depFile = $dir->getFile('dependencies.php')) instanceof \Webforge\Common\System\File && $depFile->exists()) {
        require $depFile;
      
        /* @TODO Config-Check */
        foreach ((array) $dependencies as $depName => $dep) {
          $type = Code::dvalue($dep['type'],'project','module');
          if ($type == 'project') {
            $this->dependencies[] = (object) array(
              'type'=>$type,
              'project'=>($project = PSC::getProjectsFactory()->getProject($depName)),
              'name'=>$depName,
              'file'=>$dep['file'] ?: PSC::getRoot()->getFile($project->getLowerName().'.phar.gz'),
              'relFile'=>new File('.'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.$project->getLowerName().'.phar.gz'),
              'build'=>FALSE
            );
          } elseif($type == 'module') {
            $this->dependencies[] = (object) array(
              'type'=>$type,
              'module'=>($module = PSC::getProject()->getModule($depName)),
              'name'=>$depName,
              'file'=>$dep['file'] ?: PSC::getRoot()->getFile($module->getLowerName().'.phar.gz'),
              'relFile'=>new File('.'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.$module->getLowerName().'.phar.gz'),
              'build'=>FALSE
            );
          }
        }

      } else {
        print "  Warning: Es gibt keine Dependencies fuer dieses Projekt! (wenigstens leer anlegen)\n";
        print "   Datei: ".$depFile.' mit $dependencies = array() erstellen'."\n";
      }
    }
    
    return $this->dependencies;
  }
}

?>