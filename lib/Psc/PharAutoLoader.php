<?php

namespace Psc;

/**
 * Der echte Autoloader für die psc-cms-library
 *
 * 
 * (innerhalb des phars, dies muss nicht ausgeführt werden sondern passiert automatisch wenn das phar mit cli buid-phar --lib="psc-cms" erstellt wurde)
 * 
 * require_once 'Psc'.DIRECTORY_SEPARATOR.'PharAutoLoader.php';
 * $phal = new PharAutoLoader();
 * $phal->init();
 */
class PharAutoLoader {
  
  public static $debugClass = NULL;
  public $dumpDebug = FALSE;

  /**
   * Dateien die schon geladen wurden
   * @var array
   */
  protected $files = array();

  protected $paths = array();

  /**
   * Hilfsfunktion für den Autoload mit SPL
   * 
   */
  public function autoLoad($class) {
    /* in dieser funktion nur pures php verwenden */
    
    $class = ltrim($class,'\\');
    /* das ist großer dreck, hier war mal ein bug, wo ich statt \\ ltrim / gemacht habe, was natürlich bullshit ist
       das gibt aber einen geilen Fehler auf unix. Die Page wird einfach white und es gibt einen
       [apc-error] Cannot redeclare psc\code\code in phar:....
       oder ähnliches. Liegt einfach daran, dass der Autoloader die Code Datei zweimal reinwuppen kann, da sie hier
       ja falsch cononicalized wird
    */
    
    if (array_key_exists($class, $this->paths)) {
      $path = $this->paths[$class];
      
      /* klasse bereits geladen? */
      //if (array_key_exists($class, $this->files) || class_exists($class, false) || interface_exists($class, false))
        //return;
    
      $path = $this->paths[$class];
      
      if ($this->dumpDebug) {
        $this->log('Canonicalized Name: "'.$class.'"');
        $this->log('Pfad zur Datei: "'.$path.'"');
      }
    
      require $path;
      //$this->files[$class] = TRUE;
      
      return TRUE; // für doctrine AnnotationRegistry
    }
    
    return FALSE;
  }

  /**
   * Registriert sich mit SPL Autoload
   * 
   */
  public function register() {
    if (function_exists('__autoload')) {
      throw new Exception('konservatives __autoload verhindert das Laden dieses autoloaders');
    }
    spl_autoload_register(array($this,'autoLoad'));
  }
  
  public function init() {
    $this->register();
  }
  
  
  /**
   * @param array $paths
   * @chainable
   */
  public function setPaths($paths) {
    $this->paths = $paths;
    return $this;
  }
  
  /**
   * Fügt Klassen die geladen werden sollen dem AutoLoader hinzu
   *
   * Schlüssel:
   *    Namespace1\\Namespace2\\ClassName
   * Werte:
   *    Namespace1/Namespace2/ClassName.php
   *
   * so und nich anders!
   *
   * Klasse also ohne \ davor
   * Pfad relativ zum Pfad ohne / davor und mit Endung
   *
   * Diese Pfade überschreiben schon bestehnde
   */
  public function addPaths(Array $paths) {
    $this->paths = array_merge($this->paths,$paths);
    return $this;
  }

  /**
   * @return array
   */
  public function getPaths() {
    return $this->paths;
  }
  
  public function log($msg) {
    // siehe PharVerboseAutoLoader
  }
}