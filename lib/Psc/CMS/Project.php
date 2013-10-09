<?php

namespace Psc\CMS;

use Psc\Code\Code;
use Webforge\Common\System\Dir;
use Webforge\Common\System\File;
use Psc\PSC;
use Psc\Config;
use Psc\Code\Generate\GClass;
use Psc\Code\Event\Event;
use Webforge\Configuration\Configuration;

class Project extends \Psc\Object implements \Psc\Code\Event\Subscriber, \Webforge\Framework\Project {
  
  const MODE_PHAR = 'phar';
  const MODE_SRC = 'src';
  
  /**
   * Der Name des Projektes
   *
   * dieser ist das Projektkürzel und ist case-sensitiv
   */
  protected $name;
  protected $lowerName = NULL;
  protected $namespace;
  
  /**
   * @var string
   */
  protected $vhostName;
  
  /**
   * Der UmsetzungsOrdner des Projektes
   *
   * @var Dir
   */
  protected $root;
  
  /**
   * @var Dir
   */
  protected $libsPath;
  
  /**
   *
   * @var bool
   */
  protected $loadedWithPhar = FALSE;
  
  public $loadedFromPackage = FALSE;

  protected $modules;
  
  /**
   * @var const MODE_*
   */
  protected $mode;
  
  /**
   * Die Pfade des Projektes
   *
   * alle Werte sind relativ zu $this->root und strings mit forwardslash 
   * @var array
   */
  protected $paths = array();
  
  /**
   * Die Verzeichniss des Projektes
   *
   * dies sind die $this->paths als Verzeichnisse gecached
   * @var Webforge\Common\System\Dir[]
   */
  protected $dirs = array();

  /**
   *
   * 'base' : z. B. http://tiptoi.philipp.zpintern/
   * @var array alle mit / am Ende
   */
  protected $urls = array();
  
  /*
   * @var bool
   */
  protected $tests = FALSE;

  /**
   * @var bool
   */
  protected $staging = FALSE;
  
  /**
   * @var bool
   */
  protected $production = NULL;
  
  /**
   * @var Configuration
   */
  protected $hostConfig;
  
  /**
   * @var Configuration
   */
  protected $config;
  
  /**
   * Der ClassLoader der die Klassen des Projektes lädt (nicht die Psc-Klassen)
   * @var ClassLoder
   */
  protected $classLoader;
  
  
  /**
   * @var string FQN
   */
  protected $userClass;
  
  /**
   * Der Code für die Bootstrap für buildPhar()
   * @var string
   */
  protected $pharBootstrapCode;
  
  /**
   * @param Dir $root das Umsetzungs Verzeichnis des Projektes. (beeinhaltet dann base)
   */
  public function __construct($name, Dir $root, Configuration $hostConfig, Array $paths, $mode = self::MODE_SRC, $staging = FALSE) {
    $this->name = $name;
    $this->root = $root;
    $this->hostConfig = $hostConfig;
    $this->mode = $mode;
    $this->paths = $paths;
    $this->staging = $staging;

    $this->modules = new Modules($this);

    $this->setUp();
  }
  
  /**
   *
   * für ableitende Klassen um nicht den Constructor zu vergimbeln
   */
  public function setUp() {
    $this->initConfiguration();
  }
  
  public function bootstrap() {
    return $this;
  }
  
  public function initConfiguration($projectConfig = NULL) {
    if (isset($projectConfig)) {
      $this->config = new Configuration(array());
      $this->config->merge($this->hostConfig, array('defaults'));
      
      $this->config->merge($projectConfig);
      return $this;
    }
    
    if (!isset($this->config)) {
      /* Überschreibe alle Werte aus der ProjectConfig mit denen aus der Hostconfig in Defaults */
      $this->config = new Configuration(array());
      $this->config->merge($this->hostConfig, array('defaults'));
      
      /* globals sind einfach immer käse ....
         denn wenn wir hier ein fremdes Projekt laden, merged das hier die
         GLOBALS conf Variablen vom aktuellen projekt mit dem fremden
      */
      if (!isset($GLOBALS['conf'])) $GLOBALS['conf'] = array();
      
      $roll = $GLOBALS['conf'];
      unset($GLOBALS['conf']);
      $project = $this;
      if ($this->mode === self::MODE_PHAR) {
        $cfg = $this->getBase()->getFile('inc.config.php');
        if ($cfg->exists()) {
          require $cfg;
        }
      } else {
        $cfg = $this->getSrc()->getFile('inc.config.php');
        
        if ($cfg->exists()) // kein bock mehr auf ständiges requiren und fehlermeldungen. wenn nicht da, dann nicht da
          require $cfg;
      }
      if (isset($conf) && is_array($conf)) {
        $projectConfig = new Configuration($conf);
      } elseif (isset($GLOBALS['conf']) && is_array($GLOBALS['conf'])) {
        $projectConfig = new Configuration($GLOBALS['conf']);
      } else {
        $projectConfig = new Configuration(array());
      }
      
      $GLOBALS['conf'] = $roll;
      
      $this->config->merge($projectConfig);
    }
  }
  
  /**
   * Gibt den passenden Service zum Projekt zurück
   *
   * @FIXME: ACHTUNG: dieser service hier bekommt das DoctrinePackage nicht richtig mit!
   */
  public function getMainService() {
    return new \Psc\CMS\Service\CMSService($this);
  }


  /**
   * @return Psc\CMS\Module
   */
  public function getModule($name) {
    return $this->modules->get($name);
  }
  
  public function bootstrapModuleIfExists($name) {
    return $this->modules->bootstrapIfExists($name);
  }
  
  public function isModuleExisting($name) {
    return $this->modules->isExisting($name);
  }
  
  public function isModuleLoaded($name) {
    return $this->modules->isLoaded($name);
  }
  
  /**
   * @return bool
   */
  public function isModule($name) {
    return $this->modules->isModule($name);
  }
  

  public function buildPhar(Dir $out, $check = FALSE, $buildName = 'default') {
    $builder = new \Psc\Code\Build\ProjectBuilder($this, $buildName);
    
    $builder->buildPhar($out, $check);
  }
  
  /**
   * Gibt den Ort zurück in dem im Projekt die psc-cms.phar.gz abgelegt werden soll
   *
   * @return File
   */
  public function getInstallPharFile() {
    if (($ipf = $this->config->get(array('build','installPharFile'), NULL)) !== NULL) {
      return new File($ipf);
    } else {
      return new File($this->getSrc(), 'psc-cms.phar.gz');
    }
  }
  
  protected function isOldStyle() {
    return $this->getRoot()->sub('base/')->exists();
  }
  
  /**
   * @return File
   */
  public function getClassFile($className) {
    if ($className instanceof GClass) $className = $className->getFQN();
    return Code::mapClassToFile($className, $this->getClassPath()->up());
  }
  
  /**
   * @return GClass
   */
  public function getClassFromFile(File $file) {
    return new GClass(Code::mapFileToClass($file, $this->getClassPath()->up()));
  }

  /**
   * @return string ohne \ davor und \ dahinter
   */
  public function getNamespace() {
    if (isset($this->namespace)) {
      return $this->namespace;
    } else {
      return $this->name;
    }
  }

  /**
   * @return string
   */
  public function getUserClass() {
    if (!isset($this->userClass)) {
      $this->userClass = $this->getModule('Doctrine')->getEntityName('User');
    }
    return $this->userClass;
  }
  
  /**
   * Gibt das Verzeichnis eines Namespaces in einem Verzeichnis zurück
   *
   * dies nimmt einfach den Namespace
   * @return Dir absolute im angegebenen $classPath oder in $this->getClassPath()
   */
  public function namespaceToPath($namespace, Dir $classPath = NULL) {
    $cp = $classPath ?: $this->getClassPath()->up();
    
    // end und anfangs backslashs weg
    $namespace = trim($namespace,'\\'); 
    
    $dir = $cp->sub(str_replace('\\','/',$namespace).'/');
    
    return $dir;
  }
  
  /**
   * Bestimmt ob für eine Klasse des Projektes ein Test erstellt werden soll, wenn diese automatisch erstellt wird
   *
   * z. B. Exceptions haben erstmal keinen Test
   */
  public function shouldAutomaticTestCreated(GClass $class) {
    if (\Webforge\Common\String::endsWith($class->getName(),'Exception')) {
      return FALSE;
    }
    
    /* more exceptions to come */
    
    return TRUE;
  }

  public function trigger(Event $event) {
    if ($event->is(\Psc\Doctrine\Module::EVENT_BOOTSTRAPPED)) {
      \Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
        '\Psc\Code\Compile\Annotations',
        (string) PSC::getProject('psc-cms')->getClassPath()->up()
      );
    }
  }


  /**
   * @param const $p  eine PSC::PATH_* Klassenkostante (jedoch ohne die PSC_CMS Dinger
   * @return Webforge\Common\System\Dir
   */
  public function getPath($p) {
    if (!array_key_exists($p, $this->dirs)) {
      
      if (!array_key_exists($p, $this->paths)) {
        throw new \Psc\Exception('Pfad: '.Code::varInfo($p).' ist unbekannt. Pfade vorhanden: '.implode(',',array_keys($this->paths)));
      }
      
      $this->dirs[$p] = $this->root->expand($this->paths[$p]);
    }
    
    return clone $this->dirs[$p];
  }

  public function dir($p) {
    return $this->getPath($p);
  }


  public function getRootDirectory() {
    return $this->getRoot();
  }


  public function getLanguages() {
    return $this->getConfiguration()->req('languages');
  }

  public function getDefaultLanguage() {
    $languages = $this->getLanguages();
    return current($languages);
  }
  
  /**
   * Gibt den Namen des Hosts zurück
   *
   * in der host-Config muss host gesetzt sein
   * @return string
   */
  public function getHost() {
    return $this->hostConfig->req('host');
  }

  public function getHostUrl($type = 'base') {
    return $type === 'cms' ? $this->getCMSBaseUrl() : $this->getBaseUrl();
  }
  
  /**
   * @return Psc\URL\SimpleURL
   */
  public function getBaseURL() {
    if (!isset($this->urls['base'])) {
      try {
      
        if (($url = $this->config->get(array('url','base'))) != NULL) {
          
        } elseif (($pattern = $this->hostConfig->get(array('url','hostPattern'))) != NULL) {
          $url = sprintf($pattern, $this->getLowerName(), $this->getName());
  
        } else {
          throw new \Psc\Exception('Keine Config Variablen gefunden');
        }
      
        /* wir machen hier einen dummen check
           ist eher so konvenient für den entwickler
        */
        if (mb_strpos($url,'http://') !== 0 ||
            mb_strpos($url,'https://') !== 0) {
          $url = 'http://'.$url;
        }
        $this->urls['base'] = new \Psc\Net\HTTP\SimpleURL($url);
      
      } catch (Exception $e) {
        throw new \Psc\Exception('Konfiguration: kann keine baseURL ermitteln. url.base in config oder url.hostPattern in host-config setzen: '.$e->getMessage());
      }
    }

    return clone $this->urls['base'];
  }
  
  public function getCMSBaseUrl() {
    $baseUrl = $this->getBaseURL();
    
    if ($this->config->get(array('project','cmsOnly')) === TRUE) {
      return $baseUrl;
    }

    if ($this->config->get(array('project','cmsUrl')) === 'cms') {
      $cmsUrl = clone $baseUrl;
      $cmsUrl->addPathPart('cms');
      return $cmsUrl;
    }

    // cms vor dem host-name einfügen
    $hostParts = $baseUrl->getHostParts();
    array_unshift($hostParts,'cms');
    $baseUrl->setHostParts($hostParts);

    return $baseUrl;
  }
  
  /**
   * Gibt die URL für Tests zurück
   *
   * tiptoi.philipp.zpintern => test.tiptoi.philipp.zpintern
   * @return Psc\Net\HTTP\SimpleURL
   */
  public function getTestURL() {
    $url = clone $this->getBaseURL();
    
    $host = $url->getHostParts();
    array_unshift($host,'test');
    $url->setHostParts($host);
    
    return $url;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * @return string
   */
  public function getLowerName() {
    return $this->lowerName ?: mb_strtolower($this->name);
  }


  /**
   * @return bool
   * @deprecated
   */
  public function getProduction() {
    return $this->isDevelopment(); // this is WRONG for backwordwards-compatibility
  }

  public function isDevelopment() {
    if (!isset($this->development)) {
      $this->development = FALSE;
      
      if ($this->hostConfig->get('development') !== NULL) 
        return $this->development = (bool) $this->hostConfig->get('development');
        
      $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : FALSE;
      if ($ua === $this->hostConfig->get('uagent-key')) {
        return $this->development = TRUE;
      }
      
      if ($this->getConfiguration()->get('developer') === TRUE) {
        return $this->development = TRUE;
      }
      
      if (PSC::isTravis()) {
        return $this->development = TRUE;
      }
    }

    return $this->development;
  }

  public function setDevelopment($bool) {
    $this->development = $bool;
    return $this;
  }

  public function getSrc() {
    return $this->getPath(PSC::PATH_SRC);
  }
  
  /**
   * Das Verzeichnis mit den Klassen des Projektes
   *
   * Umsetzung\base\src\GREG
   * psc-cms\Umsetzung\base\src\psc\class\Psc
   * @return Dir
   */
  public function getClassPath() {
    return $this->getPath(PSC::PATH_CLASS);
  }

  public function getTestsPath() {
    return $this->getPath(PSC::PATH_TESTS);
  }
  
  public function getBuildPath($buildName = NULL) {
    $build = $this->getPath(PSC::PATH_BUILD);
    if (isset($buildName)) {
      return $build->sub($buildName.'/');
    }
    return $build;
  }

  public function getHtdocs() {
    return $this->getPath(PSC::PATH_HTDOCS);
  }

  public function getBase() {
    return $this->getPath(PSC::PATH_BASE);
  }

  public function getFiles() {
    return $this->getPath(PSC::PATH_FILES);
  }

  public function getTestdata() {
    return $this->getPath(PSC::PATH_TESTDATA);
  }

  public function getBin() {
    return $this->getPath(PSC::PATH_BIN);
  }
  
  public function getLibsPath() {
    if (!isset($this->libsPath)) {
      $this->libsPath = PSC::getRoot();
    }
    return $this->libsPath;
  }
  
  public function setLibsPath($path) {
    if ($path instanceof Dir) {
      $this->libsPath = $path;
    } else {
      $this->libsPath = new Dir($path);
    }
    return $this;
  }

    /**
   * @return Dir
   */
  public function getVendor() {
    return $this->getPath(PSC::PATH_VENDOR);
  }
  
  /**
   * @return Dir
   */
  public function getComposerRoot() {
    return $this->getVendor()->up();
  }

  public function getTpl() {
    return $this->getPath(PSC::PATH_TPL);
  }

  public function getCache() {
    return $this->getPath(PSC::PATH_CACHE);
  }
  
  public function getConfiguration() {
    return $this->config;
  }
  
  public function setRoot(Dir $root) {
    $this->dirs = array();
    $this->libsPath = NULL;
    $this->root = $root;
    return $this;
  }
  
  public function getRoot() {
    return $this->root;
  }
  
  /**
   * @param string $vhostName
   * @chainable
   */
  public function setVhostName($vhostName) {
    $this->vhostName = $vhostName;
    return $this;
  }

  /**
   * @return string
   */
  public function getVhostName() {
    return $this->vhostName;
  }

  /**
   * @param bool $staging
   * @chainable
   */
  public function setStaging($staging) {
    $this->staging = $staging;
    return $this;
  }

  /**
   * @return bool
   */
  public function isStaging() {
    return $this->staging;
  }

  public function getStatus() {
    if ($this->isStaging()) {
      $status = 'staging';
    } elseif($this->getProduction()) {
      $status = 'production';
    } else {
      $status = 'live';
    }

    return $status;
  }
}
