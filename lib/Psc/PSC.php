<?php 

namespace Psc;

use Psc\CMS\ProjectMain,
    Webforge\Common\System\File,
    Webforge\Common\System\Dir,
    Psc\System\System,
    Psc\Code\Code,
    Psc\CMS\ProjectsFactory,
    Psc\GPC
;

use Webforge\FatalErrorHandler;

class PSC {

  const PATH_HTDOCS = 'htdocs';
  const PATH_BASE = 'base';
  const PATH_CACHE = 'cache';
  const PATH_SRC = 'src';
  const PATH_CLASS = 'class';
  const PATH_BIN = 'bin';
  const PATH_TPL = 'tpl';
  const PATH_TESTDATA = 'testdata';
  const PATH_TESTS = 'tests';
  const PATH_FILES = 'files';
  const PATH_BUILD = 'build';
  const PATH_VENDOR = 'vendor';  
  const PATH_PSC_CMS = 'psc-cms';
  const PATH_PSC_CMS_SRC = 'psc-cms-src';
  const PATH_PSC_CMS_BIN = 'psc-cms-bin';
  const PATH_PSC_CMS_FILES = 'psc-cms-files';
  const ROOT = 'root';
  const ENV_HOST = 'env_host';

  /**
   * @var Psc\Environment
   */
  protected static $environment = NULL;
  
  
  /**
   * Das aktive Projekt
   *
   * wird bei $project->bootstrap() gesetzt
   * @var Psc\CMS\Project
   */
  protected static $project;
  
  /**
   * @var Psc\CMS\ProjectsFactory
   */
  protected static $projectsFactory = NULL;
  
  /**
   * @var \Psc\CMS
   */
  protected static $cms = NULL;
  
  /**
   * Cache für den Root
   *
   * im Root liegt die host-config und das phar der Library
   * @var \Webforge\Common\System\Dir
   */
  protected static $root;
  
  
  /**
   * Wird von der Phar-bootstrap gesetzt und kann dann für weiteres (schnelles) Klassenladen benutzt werden
   *
   * @see PharAutoLoader->addPaths()
   * @var Psc\PharAutoLoader
   */
  protected static $autoLoader;
  
  /**
   * Wird gesetzt sobald inProduction() das erste Mal aufgerufen wird
   * @var bool
   */
  public static $production = NULL;
  
  /**
   * @var bool
   */
  public static $tests = FALSE;
  
  /**
   * @var Psc\Code\Event\Manager
   */
  public static $eventManager;
  
  /**
   * @return Psc\Environment
   */
  public static function getEnvironment() {
    if (!isset(self::$environment)) {
      self::$environment = new Environment();
    }
    
    return self::$environment;
  }
  
  /**
   * Gibt den Pfad zurück indem sich aktuelle JQUery, JQuery-UI usw Libraries befinden
   *
   * Dies ist für den Installer interessant
   * @return Dir
   */
  public static function getLibraryFilesPath() {
    if (self::inProduction()) throw new Exception('deprecated call');
    return PSC::getProjectsFactory()->getProject('psc-cms')->getFiles()->sub('libraries/');
  }

  /**
   * @return bool
   */
  public static function inProduction() {
    return self::getProject()->getProduction();
  }
  
  /**
   * Gibt das Kürzel des Hosts auf dem das CMS ausgeführt wird zurück
   *
   * @return string
   */
  public static function getHost() {
    if (self::inProduction()) throw new Exception('deprecated call: PSC::getProject()->getHost() benutzen');
    return self::getProject()->getHost(); 
  }
  
  public static function getRoot() {
    if (!isset(self::$root)) {
      if (($root = getenv('PSC_CMS')) == FALSE) {
        throw MissingEnvironmentVariableException::factory('PSC_CMS', 'Die Variable muss auf ein Verzeichnis in dem die psc-cms.phar liegt zeigen.');
      }
    
      try {
        self::$root = new Dir($root);
      } catch (\Webforge\Common\System\Exception $e) {
        throw MissingEnvironmentVariableException::factory('PSC_CMS', 'Die Variable muss auf ein Verzeichnis in dem die psc-cms.phar liegt zeigen. '.$e->getMessage());
      }
    }
    return self::$root;
  }
  
  public static function setAutoLoader($autoLoader) {
    self::$autoLoader = $autoLoader;
  }
  
  public static function getAutoLoader() {
    return self::$autoLoader;
  }
  
  /**
   * @return Webforge\Common\System\Dir
   */
  public static function getResources() {
    return self::getRoot()->sub('resources/');
  }
  
  /**
   * @return bool
   */
  public static function inTests() {
    return self::getProject()->getTests();
  }
  
  /**
   * wird in der auto.prepend aufgerufen
   */
  public static function registerTools() {
    $tool = GPC::GET('psc-cms-tools');
    
    if ($tool == 'autocopy') {
      echo "autocopy from library<br />";
      
      foreach (self::getAllUsedClassFiles() as $class=>$relPath) {
        AutoLoader::copyFile($class,$relPath);
      }
      exit;
    }
    
    if ($tool == 'update-schema') {
      $force = GPC::GET('force') != NULL ? Doctrine\Helper::FORCE : NULL;

      if ($force == Doctrine\Helper::FORCE) {
        print 'Updating Schema (forced) '."<br />";
      } else {
        print 'Printing Update-Schema SQL: <br />';
      }
      
      print Doctrine\Helper::updateSchema($force);
      exit;
    }

    if ($tool == 'create-schema') {
      $force = GPC::GET('force') != NULL ? Doctrine\Helper::FORCE : NULL;
      
      if ($force == Doctrine\Helper::FORCE) {
        print 'Creating Schema (forced) '."<br />";
      } else {
        print 'Printing Create-Schema SQL: <br />';
      }
      
      print Doctrine\Helper::createSchema($force);
      exit;
    }
  }
  
  /**
   * @return Psc\ErrorHandler
   */
  public static function registerErrorHandler() {
    $eh = self::getEnvironment()->getErrorHandler();
    
    return $eh->register()->setRecipient(Config::getDefault(array('debug','errorRecipient','mail'),NULL));
  }


  public static function registerFatalErrorHandler() {
    if (self::getEnvironment()->getFatalErrorHandler() === NULL) {
      $recipient = Config::getDefault(array('debug','errorRecipient','mail'), NULL);

      self::getEnvironment()->setFatalErrorHandler(
        $handler = new FatalErrorHandler($recipient)
      );

      $handler->register();
    }
  }
  
  public static function unregisterErrorHandler() {
    return self::getEnvironment()->getErrorHandler()->unregister();
  }
  
  /**
   * @return Psc\ErrorHandler
   */
  public static function getErrorHandler() {
    return self::getEnvironment()->getErrorHandler();
  }
  
  public static function registerExceptionHandler() {
    /* Exception Handling schöner */
    Exception::registerHandler();
  }
  
  /**
   * @return Psc\CMS
   */
  public static function getCMS() {
    if (!isset(self::$cms)) {
      self::$cms = new ProjectMain(self::getEnvironment());
    }
    return self::$cms;
  }
  
  /**
   * Gibt alle Dateien aus dem Psc-CMS (nicht dem Projekt) zurück
   *
   * im nicht-DEV Modus gibt dies dasselbe zurück wie getAllUsedFiles()
   * die Values sind schon relative Pfade zu base/src/
   * @return Array Schlüssel ist der voll qualifizierte Klassenname
   * @TODO move this to projects, psc-cms ist dann ein spezielles Projekt!
   */
  public static function getAllClassFiles() {
    $classPath = self::getProjectsFactory()->getProject('psc-cms')->getClassPath()->up();
    
    $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($classPath),
                \RecursiveIteratorIterator::LEAVES_ONLY
      );
    
    $files = array();
    foreach ($iterator as $file) {
      $sourceFile = realpath($file->getPathName());
      if (mb_strpos($sourceFile,DIRECTORY_SEPARATOR.'.svn') !== FALSE) continue;
      
      $f = new File($sourceFile);
      print $f."\n";
      
      $className = self::getFullClassName($f, $classPath);
    
      $files[$className] = $f;
    }
    return $files;
  }
  
  
  /**
   * Gibt alle PSC-CMS Library-Dateien aus dem Projekt zurück
   * 
   * @TODO move this to projects
   * @return Array Schlüssel ist der voll qualifizierte Klassenname, wert ist der relative Pfad zum psc-cms/base/src Pfad als String! (das ist andes als bei getAllClassFiles)
   */
  public static function getAllUsedClassFiles() {
    $classPath = self::getProject()->getClassPath()->up();
    
    $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($classPath),
                \RecursiveIteratorIterator::LEAVES_ONLY
      );
    
    $files = array();
    foreach ($iterator as $file) {
      $sourceFile = realpath($file->getPathName());
      
      if (mb_strpos($sourceFile,DIRECTORY_SEPARATOR.'.svn') !== FALSE) continue;
      
      $f = new File($sourceFile);
      $className = self::getFullClassName($f, $classPath);
      
      $relPath = mb_substr($sourceFile, mb_strlen($classPath));
      $files[$className] = $relPath;
    }
    return $files;
  }
  
  /**
   * @return Psc\CMS\ProjectsFactory
   */
  public static function getProjectsFactory() {
    if (!isset(self::$projectsFactory)) {
      throw new Exception('ProjectsFactory wurde nicht initialisiert. Wurde das psc-cms.phar geladen?');
    }
    return self::$projectsFactory;
  }
  
  
  public static function setProjectsFactory(\Psc\CMS\ProjectsFactory $factory = NULL) {
    self::$projectsFactory = $factory;
  }
  
  public static function setProject(\Psc\CMS\Project $project) {
    self::$project = $project;
    return $project;
  }
  
  public static function getProject() {
    return self::$project;
  }
  
  
  /**
   * 
   * @return Psc\Code\Event\Manager
   */
  public static function getEventManager() {
    if (!isset(self::$eventManager)) {
      self::$eventManager = new \Psc\Code\Event\Manager();
    }
    return self::$eventManager;
  }
  
  /**
   * Gibt den Dateinamen für eine Klasse zurück
   *
   * es wird versucht mit dem ersten Teil des Namespaces das Projekt/Modul der Klasse herauszufinden
   * Es wird das "oldschool" Format benutzt wie Swift_Message oder das neue wie: SerienLoader\Manager
   * es werden Projekte und Module untersucht
   */
  public static function getClassFile($className, $projectHint = NULL, $moduleHint = NULL) {
    $className = ltrim($className,'\\');
    
    if (isset($projectHint)) {
      return self::getProjectsFactory()->getProject($projectHint)->getClassName($className);
    }
    
    if (isset($moduleHint)) {
      return self::getProject()->getModule($moduleHint)->getClassName($className);
    }
    
    if (mb_strpos($className,'Psc\\') === 0) {
      $pr = self::getProjectsFactory()->getProject('psc-cms');
      return $pr->getClassFile($className);
    }
    
    $tries = array();
    list($rootNS) = explode('\\',$className,1);
    if (!empty($rootNS)) $tries[] = $rootNS;
    list($rootNS) = explode('_',$className,1);
    if (!empty($rootNS)) $tries[] = $rootNS;
    
    foreach ($tries as $rootNS) {
      try {
        $project = self::getProjectsFactory()->getProject($rootNS);
        return $project->getClassFile($className);
      } catch (\Psc\ProjectNotFoundException $e) {
      }
      
      try {
        $module = self::getProject()->getModule($rootNS);
        return $module->getClassFile($className);
      } catch(\Psc\ModuleNotFoundException $e) {
      }
    }
    
    throw new Exception('kann die Datei nicht für: '.$className.' ermitteln. Es wurden die Namespaces: '.implode(',',$tries).' durchsucht');
  }


  /**
   * Gibt den vollen Namen der Klasse aus der Datei zurück
   * 
   * dies parsed nicht den Code der Datei, sondern geht von der Klassen-Struktur wie in Konventionen beschrieben aus
   * @return string
   */
  public static function getFullClassName(File $classFile, Dir $classPath = NULL) {
    $classPath = $classPath ?: self::getProject()->getClassPath()->up();
  
    try {
      $parts = $classFile->getDirectory()->makeRelativeTo($classPath)->getPathArray();
    } catch (\Webforge\Common\System\Exception $e) {
      throw new Exception('ClassFile: "'.$classFile.'" liegt nicht im Verzeichnis: "'.$classPath.'"!',0,$e);
    }
    array_shift($parts); // den . entfernen
    $parts[] = $classFile->getName(File::WITHOUT_EXTENSION);
    
    $className = '\\'.implode('\\',$parts);
    return $className;
  }
   
  /**
   *
   */
  public static function get($name) {
    
    /* Projektverzeichnisse */
    if (in_array($name, array(self::PATH_SRC, self::PATH_HTDOCS, self::PATH_BASE, self::PATH_FILES, self::PATH_TESTDATA, self::PATH_BIN, self::PATH_TPL, self::PATH_CACHE))) {
      return self::getProject()->getPath($name);
    }
      
    /* psc-cms Pfade */
    if (array_key_exists($name,$ps = array(self::PATH_PSC_CMS => self::PATH_BASE,
                                     self::PATH_PSC_CMS_SRC => self::PATH_SRC,
                                     self::PATH_PSC_CMS_BIN => self::PATH_BIN,
                                     self::PATH_PSC_CMS_FILES => self::PATH_FILES))) {
      return self::getProjectsFactory()->getProject('psc-cms')->getPath($ps[$name]);
    }
    
    if ($name == self::ENV_HOST) {
      return self::getHost();
    }
    
    if ($name == self::ROOT) {
      return self::getRoot();
    }
    
    throw new Exception('unbekannter Name für get():'.Code::varInfo($name));
  }

  /**
   *
   * Versionen:
   *
   * 0.1[-dev] ist die Standard-Version und ist z. B. in tiptoi-verbaut: (26.01.2012)
   * 0.2[-dev] ist die nächste Version. Mit TabsContentItem2 und vielem neuen Kram, diese verhält sich an vielen Stellen
   * anders als die 0.1-dev
   *
   * prüfen:
   * PSC::getVersion()->is('>','0.1');
   * oder halt
   * PSC::getVersion()->is('>=','0.2');
   * was ich persönlich lesbarer finde
   *
   * @return Psc\Version
   */
  public static function getVersion(\Psc\CMS\Project $project = NULL) {
    if (!isset($project)) $project = self::getProject();
    return new Version($project->getConfiguration(), 'psc-cms');
  }
  
  /**
   *
   * wenn schon exit dann wenigstens diese Funktion nehmen
   */
  public static function terminate($returnCode = 1) {
    if (!self::inProduction() && !self::inTests()) {
      exit($returnCode);
    }
  }
  
  public static function isTravis() {
    return getenv('TRAVIS') === 'true';
  }
}
?>