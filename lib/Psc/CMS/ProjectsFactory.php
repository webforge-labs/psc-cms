<?php

namespace Psc\CMS;

use Webforge\Common\System\Dir;
use Psc\PSC;
use Phar;

class ProjectsFactory extends \Psc\Object {
  
  const MODE_SRC = Project::MODE_SRC;
  const MODE_PHAR = Project::MODE_PHAR;
  
  protected $projects = array();
  
  /**
   * Die Klassen für das Projekt-Objekt für jedes projekt (nach Name)
   *
   */
  protected $projectsClasses = array(
    'psc-cms'=>'Psc\Project'
  );
  
  protected $projectsRoot;
  
  protected $paths = array();
  
  /**
   * @var Psc\CMS\Configuration
   */
  protected $hostConfig;
  
  public function __construct(Configuration $hostConfig) {
    $this->hostConfig = $hostConfig;
  }

  public function getProject($name, $mode = Project::MODE_SRC, $staging = FALSE) {
    
    if (!array_key_exists($name, $this->projects)) {
      // root
      $root = $this->getProjectRoot($name, $mode);
      
      if (!$root->exists()) {
        throw new \Psc\ProjectNotFoundException('Projekt mit dem Namen: '.$name.' nicht gefunden. (Verzeichnis existiert nicht: '.$root.')');
      }
    
      $paths = $this->getProjectPaths($name, $mode);
      
      // instance
      $this->projects[$name] = $this->getProjectInstance($name, $root, $this->hostConfig, $paths, $mode, $staging);
    }
    
    return $this->projects[$name];
  }
  
  /**
   * Gibt ALLE Pfade für das Projekt zurück
   *
   * wenn vorher pfade mit setProjectPath gesetzt wurden, werden diese nie überschrieben (auch wenn der modus ein anderer ist)
   * alle anderen Pfade werden mit defaults gesetzt (je nach modus)
   */
  public function getProjectPaths($name, $mode = self::MODE_SRC) {
    if (!array_key_exists($name, $this->paths)) {
      $this->paths[$name] = array();
    }
    
    $defaultPaths = array();
    if ($mode === self::MODE_SRC) {
      $defaultPaths[PSC::PATH_SRC] = './base/src/';
      $defaultPaths[PSC::PATH_HTDOCS] = './base/htdocs/';
      $defaultPaths[PSC::PATH_BASE] = './base/';
      $defaultPaths[PSC::PATH_CACHE] = './base/cache/';
      $defaultPaths[PSC::PATH_BIN] = './base/bin/';
      $defaultPaths[PSC::PATH_TPL] = './base/src/tpl/';
      $defaultPaths[PSC::PATH_TESTDATA] = './base/files/testdata/';
      $defaultPaths[PSC::PATH_TESTS] = $name === 'psc-cms' ? './base/src/psc/tests/' : './base/src/'.$name.'/tests/';
      $defaultPaths[PSC::PATH_CLASS] = $name === 'psc-cms' ? './base/src/psc/class/Psc/' : './base/src/'.$name.'/';
      $defaultPaths[PSC::PATH_FILES] = './base/files/';
      $defaultPaths[PSC::PATH_BUILD] = './base/build/';
    } elseif ($mode === self::MODE_PHAR) {
      $defaultPaths[PSC::PATH_SRC] = './'; // bedeutet das verzeichnis vom phar.gz
      $defaultPaths[PSC::PATH_HTDOCS] = './www/';
      $defaultPaths[PSC::PATH_BASE] = './';
      $defaultPaths[PSC::PATH_CACHE] = './cache/';
      $defaultPaths[PSC::PATH_BIN] = './';
      $defaultPaths[PSC::PATH_TPL] = './tpl/';
      $defaultPaths[PSC::PATH_TESTDATA] = './files/testdata/';
      $defaultPaths[PSC::PATH_CLASS] = './lib/';
      $defaultPaths[PSC::PATH_TESTS] = './tests/';
      $defaultPaths[PSC::PATH_FILES] = './files/';
      $defaultPaths[PSC::PATH_BUILD] = './build/';
    }
    
    return array_replace($defaultPaths, $this->paths[$name]);
  }
  
  /**
   * @param string $name ProjektName
   * @param const|string PSC::PATH_XX string: XX
   * @param string $value wenn mit ./ oder wird der pfad relativ zum projectRoot interpretiert. Immer forwardSlash! mit trailingslash
   */
  public function setProjectPath($name, $key, $value) {
    if (!is_string($value)) throw new \InvalidArgumentException('Nur string für $value erlaubt. Relative mit ./ beginnen');
    if ($value === '../') throw new \InvalidArgumentException('Für relative Pfade immer ./ benutzen => ../ => ./../');
    if (is_string($key)) {
      $key = constant(sprintf('Psc\PSC::PATH_%s',mb_strtoupper($key)));
    }
    $this->paths[$name][$key] = $value;
    return $this;
  }
  
  /**
   * Interne Methode für die Erstellung eines Projektes
   * 
   * nicht diese Methode nehmen um ein Projekt zu erhalten. Dann getProject() nehmen
   */
  public function getProjectInstance($name, Dir $root, Configuration $hostConfig, Array $paths, $mode = Project::MODE_SRC, $staging = FALSE) {
    $c = array_key_exists($name, $this->projectsClasses) ? $this->projectsClasses[$name] : 'Psc\CMS\Project';
    
    return new $c($name, $root, $hostConfig, $paths, $mode, $staging);
  }
  
  /**
   * Macht keine Checks ob das Projekt existiert
   *
   * @return Dir
   */
  public function getProjectRoot($name, $mode = Project::MODE_SRC) {
    /* root
      
      entweder ist projects.$name.root in der host-config gesetzt, oder es wird angenommen
      dass das projekt in
      CONFIG[projects.root]/$name/Umsetzung
      ist
      
      wenn der Mode PHAR ist, wird das Phar base Directory genommen
      
      wir müssen hier noch unterscheiden, wenn das phar aus dem bin verzeichnis aufgerufen werden sollte
      @TODO siehe ePaper42
    */
    if ($mode === Project::MODE_PHAR) {
      $root = new Dir(PHAR_ROOT); // siehe ProjectBuilder
    } elseif (($proot = $this->hostConfig->get(array('projects',$name,'root'))) != NULL)
      $root = new Dir($proot);
    else
      $root = $this->getProjectsRoot()->sub($name.'/Umsetzung/');
      
    return $root;
  }
  
  public function getProjectClassPath($name, Dir $root, $mode = self::MODE_SRC) {
    $paths = $this->getProjectPaths($name, $mode);
    
    return $root->expand($paths[PSC::PATH_CLASS]);
  }
  
  /**
   * Gibt das Verzeichnis zurück in dem sich die psc-Projekte befinden
   *
   * insbesondere sollte dor psc-cms als Verzeichnis drin liegen mit dem Repository fürs CMS
   * @return Dir
   */
  public function getProjectsRoot() {
    if (!isset($this->projectsRoot)) {
      $this->projectsRoot = new Dir($this->hostConfig->req('projects.root'));
    }
    
    return $this->projectsRoot;
  }
  
  /**
   * @param string $name der Name des Projektes
   * @param string $class der Volle Name zur Klasse
   */
  public function setProjectClass($name, $class) {
    $this->projectsClasses[$name] = $class;
    return $this;
  }
  
  protected function getPaths() {
    return $this->paths;
  }
  
  public function getHostConfig() {
    return $this->hostConfig;
  }
}
?>