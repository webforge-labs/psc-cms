<?php

namespace Psc\CMS;

use Psc\ClassLoader;
use Webforge\Common\System\File;
use Psc\PSC;

abstract class Module extends \Psc\Object {
  
  protected $project;
  
  protected $standardClassLoader;
  
  protected $name;
  
  public function __construct(Project $project) {
    $this->project = $project;
  }
  
  public function setProject(Project $project) {
    $this->project = $project;
    return $this;
  }
  
  /**
   * @return Psc\Code\Build\ModuleBuilder
   */
  public function buildPhar(File $out, $check, $buildName) {
    $builder = new \Psc\Code\Build\ModuleBuilder($this);
    $builder->buildPhar($out);
    return $builder;
  }
  
  /**
   * Erlaubt es dem Module-Phar zusätzlich Files hinzufügen
   *
   * die Rückgabe ist ein Array mit dem Schlüssel als absolute URL im Phar und der Wert die reale Datei als \Webforge\Common\System\File
   *
   * z. B.
   * return array(
   *  '/img/blubb.png' => new \Webforge\Common\System\File(PSC::getProject()->htdocs()->sub('img/')->getFile('blubb.png'));
   * );
   */
  public function getAdditionalPharFiles() {
    return array();
  }

  /**
   * Gibt eine Liste von physikalischen Dateien mit Klassen zurück, die dem Phar hinzugefügt werden sollen
   *
   * dies ist z. B. praktisch um so komische nicht PSR-0 Klassen hinzuzufügen
   *
   * gibt einen Array zurück dessen Werte \Webforge\Common\System\Files sind der Klassename wird mit Code::mapFileToClass ermittelt
   */
  public function getAdditionalClassFiles() {
    return array();
  }
  
  /**
   * @return array
   */
  abstract public function getModuleDependencies();

  /**
   * Das Verzeichnis in dem die Klassen liegen nicht das Parent-Verzeichnis davon
   *
   * also doctrine-orm/Doctrine und nicht doctrine-orm
   * @return Webforge\Common\System\Dir
   */
  abstract public function getClassPath();

  /**
   * @return string
   */
  abstract public function getNamespace();
  
  /**
   * @return File
   */
  public function getClassFile($className) {
    return Code::mapClassToFile($className, $this->classPath);
  }


  public function getAutostartFile() {
    return NULL;
  }
  
  public function bootstrapStandardClassLoader() {
    $this->standardClassLoader = new ClassLoader($this->getNamespace(),
                                                 $this->getClassPath()->up()
                                                );
    $this->standardClassLoader->register();
  }
  
  public function getProject() {
    return $this->project;
  }
  
  public function getName() {
    return $this->name;
  }
  
  public function getLowerName() {
    return mb_strtolower($this->name);
  }
  
  /**
   * Gibt die Phar-Datei zurück durch die das Modul geladen wurde
   */
  public function getPharFile() {
    return $this->project->getLibsPath()->getFile($this->getLowerName().'.phar.gz');
  }
  
  public function loadPharResource($relativePath) {
    require_once $this->getPharResource($relativePath);
  }
  
  public function getPharResource($relativePath = NULL) {
    return 'phar://'.$this->getPharFile().'/'.ltrim($relativePath,'/');
  }
  
  /**
   * sollte unbedingt dispatchBootstrapped() am Ende aufrufen
   */
  abstract public function bootstrap($bootFlags = 0x000000);
  
  
  /**
   * Sollte unbedingt nach bootstrap() aufgerufen werden
   */
  public function dispatchBootstrapped() {
    PSC::getEventManager()->dispatchEvent('Psc.ModuleBootstrapped', NULL, $this);
    PSC::getEventManager()->dispatchEvent('Psc.'.$this->getName().'.ModuleBootstrapped', NULL, $this);
  }
}
?>