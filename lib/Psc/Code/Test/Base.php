<?php

namespace Psc\Code\Test;

use Psc\PSC;
use Webforge\Common\System\Dir;
use Webforge\Common\System\File;
use Webforge\Common\System\Util as SystemUtil;
use Closure;
use Psc\System\Console\Process;
use Webforge\Translation\ArrayTranslator;
use Psc\CMS\Translation\Container as TranslationContainer;
use Webforge\Translation\TranslationsBuilder;

/**
 */
class Base extends \Webforge\Code\Test\Base {
  
  /**
   * @var FrontendCodeTester (CodeTester)
   */
  protected $test;

  /**
   * @var Psc\CMS\Project
   */
  protected $project;
  
  /* copy n paste zu DatabaseTest */
  protected $resourceHelper;
  
  protected $doublesManager;
  
  public function __construct($name = NULL, array $data = array(), $dataName = '') {
    parent::__construct($name, $data, $dataName);
    
    $this->test = new FrontendCodeTester($this);
    $this->doublesManager = new DoublesManager($this);
  }
  
  public function getProject() {
    if (!isset($this->project)) {
      $this->project = $GLOBALS['env']['container']->getProject();
    }
    return $this->project;
  }

  /**
   * @return the local package
   */
  public function getPackage() {
    return $GLOBALS['env']['container']->webforge->getLocalPackage();
  }

  public function getPackageDir($sub) {
    return $this->getPackage()->getRootDirectory()->sub($sub);
  }
  
  public function getHostConfig() {
    return $GLOBALS['env']['container']->getHostConfig();
  }
  
  public function getCodeTester() {
    return $this->test;
  }

  public function initAcceptanceTester($tester) {
  }

  /**
   * @return Psc\Code\Test\ResourceHelper
   */
  public function getResourceHelper() {
    if (!isset($this->resourceHelper)) {
      $this->resourceHelper = new ResourceHelper(PSC::getProject());
    }
    return $this->resourceHelper;
  }

  /**
   * Hilft bei TestDoubles und hat jede Menge immer wiederkehrende TestFälle
   *
   * @return Psc\Code\Test\DoublesManager
   */
  public function getDoublesManager() {
    return $this->doublesManager;
  }
  
  /**
   * Gibt das "persönliche" Verzeichnis für den Test im Filesystem zurück
   *
   * Erstellt das Directory wenn es nicht existiert
   */
  public function getTestDirectory($subDir = NULL) {
    $dir = $this->getResourceHelper()->getTestDirectory($this);
    
    if (isset($subDir))
      $dir = $dir->sub($subDir);
      
    $dir->create();
    return $dir;
  }
  
  /**
   * @param $directory kann fixture sein oder common
   * @return Webforge\Common\System\File (existiert)
   */
  public function getFile($name, $subDir = '/', $directory = 'fixture') {
    $dir = $directory === 'common' ? $this->getResourceHelper()->getCommonDirectory() : $this->getTestDirectory();
    $file = $dir->sub($subDir)->getFile($name);
    
    $msg = 'Kann Datei: "'.$name.'" nicht in subDir '.$subDir.' in: '.$dir.' finden';
    $this->assertInstanceOf('Webforge\Common\System\File', $file, $msg);
    $this->assertFileExists((string) $file, $msg);
    
    return $file;
  }
  
  /**
   * @return Webforge\Common\System\File (existiert)
   */
  public function getCommonFile($name, $subDir = 'files/') {
    return $this->getFile($name, $subDir, 'common');
  }
  
  /**
   * @return Webforge\Common\System\File
   */
  public function newFile($name, $subDir = '/', $directory = 'fixture') {
    $dir = $directory === 'common' ? $this->getResourceHelper()->getCommonDirectory() : $this->getTestDirectory();
    return $dir->sub($subDir)->make(Dir::PARENT | DIR::ASSERT_EXISTS)->getFile($name);
  }
  
  /**
   * @return Psc\CMS\EntityMeta
   */
  public function getEntityMeta($entityName, \Psc\Doctrine\Module $module = NULL) {
    if (!isset($module)) {
      if (isset($this->dc)) {
        return $this->dc->getEntityMeta($entityName);
      } else {
        return PSC::getProject()->getModule('Doctrine')->getEntityMeta($entityName);
      }
    }
    
    return $module->getEntityMeta($entityName);
  }
  
  /**
   * @param $name der Name der Entities in klein in plural
   */
  public function loadTestEntities($name, \Psc\Doctrine\Module $module = NULL) {
    //$module = $module ?: PSC::getProject()->getModule('Doctrine');
    
    //$class = $module->getEntityName(\Psc\Inflector::singular($name));
    // $this->loadEntity($class, $module); // mittlerweile automatisch
    
    // vll auch ergebnis einen rausnehmen und davon die klasse laden?
    return $this->getResourceHelper()->getEntities($name);
  }

  public function loadEntity($entityClass, \Psc\Doctrine\Module $module = NULL) {
    $module = $module ?: PSC::getProject()->getModule('Doctrine');
    $module->registerEntityClassesMetadataDriver()->getEntityClassesMetadataDriver()->addClass($entityClass);
    return $this;
  }

  public function createType($typeName) {
    if ($typeName instanceof \Psc\Data\Type\Type) return $typeName;
    
    return \Psc\Data\Type\Type::create($typeName);
  }
  
  public function getType($typeName) {
    return $this->createType($typeName);
  }
  
  /**
   * Erstellt einen Request zur BaseURL mit den Credentials in der Host-Config
   *
   * ist das deprecated? wegen RequestDispatcher?
   * @param string $contentType  html|json
   * @return Psc\URL\Request
   */
  public function createCMSRequest($relativeURL, $contentType = NULL, $baseURL = NULL, \Psc\CMS\Configuration $hostConfig = NULL) {
    $url = $baseURL ?: $this->getProject()->getBaseURL();
    $url .= ltrim($relativeURL,'/');
    
    $hostConfig = $hostConfig ?: $this->getHostConfig();
    
    $curl = new \Psc\URL\Request($url);
    $curl->setAuthentication($hostConfig->req('cms.user'),$hostConfig->req('cms.password'),CURLAUTH_BASIC);
    $curl->setHeaderField('X-Psc-Cms-Connection','tests');
    if ($hostConfig->get('uagent-key') != NULL) {
      $curl->setHeaderField('Cookie', 'XDEBUG_SESSION='.$hostConfig->get('uagent-key'));
    }
    
    if (isset($contentType)) {
      if (mb_strtolower($contentType) === 'html') {
        $curl->setHeaderField('Accept', 'text/html');
      } elseif (mb_strtolower($contentType) === 'json') {
        $curl->setHeaderField('Accept', 'application/json');
      } else {
        $curl->setHeaderField('Accept', $contentType);
      }
    }
    
    return $curl;
  }
  
  protected function debugCollection($collection, $label = NULL) {
    return \Psc\Doctrine\Helper::debugCollection($collection, "\n", $label);
  }

  /**
   * Gibt eine Collection von Objekten auf ein Feld reduziert zurück
   *
   */
  public function reduceCollection($collection, $field = 'identifier') {
    return \Psc\Doctrine\Helper::map($collection, $field);
  }
  
//  protected function onNotSuccessfulTest(\Exception $e) {
//    print \Webforge\Common\ArrayUtil::join($this->sjg->log, "\n  %s");
//    throw $e;
//  }

  public function createTranslationContainer(TranslationsBuilder $translations, $currentLocale = 'de') {
    $translator = new ArrayTranslator($currentLocale, array());
    $translator->addDomainTranslations($translations->build(), $translations->getDomain());

    return new TranslationContainer($translator);
  }
}
