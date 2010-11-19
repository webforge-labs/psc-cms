<?php

if (!defined("FLUIDGRID_PATH")) define("FLUIDGRID_PATH", dirname(__FILE__));

class FluidGrid_AutoLoader {
  protected static $frameworkCoreClasses = array('utf8','html','pscCMS');

  /**
   * Dateien die schon geladen wurden
   * @var array
   */
  protected static $files = array();
  
  /**
   * @param string Name der Klasse FluidGrid_ immer weglassen
   */
 public static function load($name) {
   if (in_array($name, self::$files) || class_exists('FluidGrid_'.$name, false) || interface_exists('FluidGrid_'.$name, false))
      return;

    $subDir = NULL;
    $nameSpace = self::getNameSpace($name);
    $filename = $name;
    if (is_dir(FLUIDGRID_PATH.DIRECTORY_SEPARATOR.$nameSpace)) {
      $subDir = DIRECTORY_SEPARATOR.$nameSpace;
      $filename = substr($name,strlen($nameSpace)); // cut off namespace
    }
    
    require_once FLUIDGRID_PATH.$subDir.DIRECTORY_SEPARATOR.$filename.".php";
    self::$files[] = $name;
  }

  /**
   * Hilfsfunktion für den Autoload mit SPL
   * 
   */
  public static function autoLoad($class) {
    /* in dieser funktion nur pures php verwenden */
    if (strpos($class,'FluidGrid_') !== 0 && !in_array($class,self::$frameworkCoreClasses))
      return FALSE;
    
    if (in_array($class,self::$frameworkCoreClasses)) {
      require_once FLUIDGRID_PATH.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.$class.".php";
      return;
    }
    /* ab hier kann php von core verwendet werden: */

    return self::load(substr($class,10));
  }

  /**
   * Gibt den möglichen NameSpace eines CamelCase Klassen-Namen zurück
   * 
   * HTMLElement => HTML
   * TableColumn => Table
   * IObjectModel => Object
   */
  protected function getNameSpace($camelCaseWord) {
    /* 
     * $camelCaseWord = 'Object';
     * $camelCaseWord = 'ObjectModel';
     * $camelCaseWord = 'IObjectModel';
     * $camelCaseWord = 'HTMLElement';
     * $camelCaseWord = 'IHTMLElement';
     * $camelCaseWord = 'IInterfaceModel';
     */
    $nameSpace = NULL;
    if (preg_match('/I?(([A-Z][a-z]+)[A-Z].+|([A-Z]+)[A-Z][a-z])+/u',$camelCaseWord,$match) > 0) {
      $nameSpace = array_pop($match);
    }
    
    return $nameSpace;
  }

  /**
   * Registriert sich mit SPL Autoload
   * 
   */
  public static function register() {
    spl_autoload_register(array(__CLASS__,'autoLoad'));
  }
}
