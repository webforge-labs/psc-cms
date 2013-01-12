<?php

namespace Psc;

use \Webforge\Common\System\File;

class DependencyManager extends OptionsObject {
  
  protected $name;
  
  /**
   * @var array alle registrierten Files mit Abhängigkeiten
   */
  protected $files = array();
  
  /**
   * Helfer für die Sortierung der Files
   * @var int
   */
  protected $sort;
  
  /**
   * @var array alle geladenen Files (bzw die, die geladen werden sollen)
   */
  protected $enqueued = array();
  
  
  protected $ignored = array();
  
  /**
   * @param string $name der Name des Managers
   */
  public function __construct($name) {
    $this->name = $name;
    $this->sort = 1;
  }
  
  /**
   * Lädt eine Datei
   *
   * @param string $alias wie bei register() angegeben
   */
  public function enqueue($alias) {
    if (in_array($alias, $this->ignored)) return $this;
    
    if (!array_key_exists($alias, $this->files)) {
      $e = new DependencyException('Alias: '.$alias.' nicht gefunden. Wurde diese Datei mit register() geladen?');
      $e->dependency = $alias;
      throw $e;
    }
    
    if (!in_array($alias, $this->enqueued)) {
      $file = $this->files[$alias];
    
      /* as naive as I can: enqueue all dependencies, if not already */
      if (!empty($file['dependencies'])) {
        foreach ($file['dependencies'] as $fileAlias) {
          $this->enqueue($fileAlias);
        }
      }
      
      $this->enqueued[] = $alias;
    }
    
    return $this;
  }
  
  public function enqueueAll() {
    foreach ($this->files as $alias => $file) {
      $this->enqueue($alias);
    }
    return $this;
  }
  
  /**
   * Sagt dem DependencyManager, dass er sich um diese Datei nicht mehr kümmern muss
   *
   */
  public function ignore($alias) {
    $this->ignored[] = $alias;
  }
  
  /**
   * Registriert eine Datei (unter einem Alias)
   *
   * diese Datei kann dann mit enqueue() geladen werden
   * @param string $file
   * @param string $alias
   * @param array $dependencies ein Array mit Aliases von dem diese Datei abhängt (die mitgeladen werden, wenn diese Datei mit enqueue() geladen wird)
   */
  public function register($file, $alias = NULL, Array $dependencies = array()) {
    if (!isset($alias)) $alias = $this->getAliasFromFile($file);
    
    $this->files[$alias] = array('name'=>$file,
                                 'dependencies'=>$dependencies,
                                 'sort'=>$this->sort
                                 );
    $this->sort++;
    
    return $this;
  }
  
  /**
   * Entfernt eine registrierte Datei
   *
   * @param string $alias wie bei register() angegeben
   */
  public function unregister($alias) {
    // unregister
    unset($this->files[$alias]);

    // aus queued entfernen
    $key = array_search($alias, $this->enqueued);
    if ($key !== FALSE) {
      unset($this->enqueued[$key]);
    }
    return $this;
  }
  
  /**
   * @return string
   */
  protected function getAliasFromFile($file) {
    $f = new File($file);
    return $f->getName(File::WITHOUT_EXTENSION);
  }
}
/**
 *
 * So gehts:
 *
 * 
    try {
      JS::register('metaobjects-1.5.1.js','metaobjects',array('jquery'));
      JS::enqueue('metaobjects');
      JS::enqueue('cms.helpers');
    } catch (\Psc\DependencyException $e) {
      
      $e->setItem('JSHelper::addMetadata');
      $e->setInfo('jQuery, metaobjects und cms.helpers');
      
      throw $e;
    }

 */
class DependencyException extends Exception {

  public $dependency;
  
  public $item;
  
  public $newMessage = '%s benötigt %s um zu funktionieren. Diese müssen mit JS::register() registriert werden. Es fehlt (mindestens): %s';
  
  public $info;
  
  /**
   * Der Name der Funktion/Methode/Klasse die eine Library benötigt
   *
   * Es muss unbedingt auch setInfo() aufgerufen werden
   * @param string $name
   */
  public function setItem($name) {
    $this->item = $name;
  }
  
  /**
   * Die Information welche Abhängigkeiten das $item zu welchen Libraries hat
   */
  public function setInfo($string) {
    $this->info = $string;
    $this->message = sprintf($this->newMessage,
                             $this->item, $this->info, $this->dependency);
  }
}
?>