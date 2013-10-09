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

  /**
   * @var Psc\Environment
   */
  protected static $environment = NULL;
  
  /**
   * Das aktive Projekt
   *
   * wird bei $project->bootstrap() gesetzt
   * @var Webforge\Framework\Project
   */
  protected static $project;
  
  /**
   * @var Psc\CMS\ProjectsFactory
   */
  protected static $projectsFactory = NULL;
  
  /**
   * Cache for Root
   *
   * im Root liegt die host-config und das phar der Library
   * @var \Webforge\Common\System\Dir
   */
  protected static $root;
  
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
   * NOTICE: this is WRONG(!) its legacy and returns TRUE if development is TRUE!
   * 
   * don't use it anmyore
   * @return bool
   */
  public static function inProduction() {
    throw new DeprecatedException('use project::isDevelopment');
  }
  
  /**
   * Returns the root directory of the host where the host-config lives
   * 
   * @return Psc\System\Directory
   * @throws Psc\MissingEnvironmentVariableException
   */
  public static function getRoot() {
    if (!isset(self::$root)) {
      try {

        $root = getenv('PSC_CMS');

        if (!empty($root)) {
          return self::$root = new Dir($root);
        }
  
      } catch (\Webforge\Common\System\Exception $e) {}

      throw MissingEnvironmentVariableException::factory('PSC_CMS', 'The variable should reference a directory, where the host-config.php can live. Use trailing slash/backslash.');
    }

    return self::$root;
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
    return $GLOBALS['env']['container']->inTests();
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
    throw new DeprecatedException('Dont use this anymore. Instantiate from Controller or else');
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
  
  public static function setProject(\Webforge\Framework\Project $project) {
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
    throw new DeprecatedException('This cannot be used anymore');
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
    throw new DeprecatedException('Dont use this anymore. Use the paths from project or the direct methods');
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
   * Stops the application immediately (emergency exit)
   */
  public static function terminate($returnCode = 1) {
    if (self::getProject()->isDevelopment() && !self::inTests()) {
      exit($returnCode);
    }
  }
  
  /**
   * @return bool
   */
  public static function isTravis() {
    return getenv('TRAVIS') === 'true';
  }
}
