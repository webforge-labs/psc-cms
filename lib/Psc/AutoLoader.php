<?php

namespace Psc;

if (!defined('SYSTEM_FILE_MOD')) define('SYSTEM_FILE_MOD',0644);

/**
 * nativer Autoloader für Production
 *
 * Dieser AutoLoader soll nur noch von cli.php benutzt werden (denn diese erstellt die phar Datei die ab da dann benutzt werden kann)
 *
 * d. h. um die eigentliche Library zu benutze: siehe PharAutoLoader und cli.php Befehl: "build-phar"
 *
 * require_once 'psc'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'Psc'.DIRECTORY_SEPARATOR.'AutoLoader.php';
 * \Psc\AutoLoader::init();
 *  
 */
class AutoLoader {
  
  public static $path = NULL;
  
  /**
   * Hilfsfunktion für den Autoload mit SPL
   * 
   */
  public static function autoLoad($class) {
    /* in dieser funktion nur pures php verwenden */
    $class = ltrim($class,'\\');
    
    if (mb_strpos($class,'Psc') !== 0)
      return FALSE;

    $path = self::$path.str_replace('\\',DIRECTORY_SEPARATOR,$class).'.php';
    
    if (!stream_resolve_include_path($path)) {
      return FALSE;
      //throw new Exception(sprintf("Pfad '%s' für Klasse '%s' konnte nicht gefunden werden. require_once() würde fehlschlagen.",$path, $class));
    }
    
    /* wir haben eine Klasse zum Laden gefunden */
    require $path;
    
    return TRUE; // für doctrine AnnotationRegistry
  }

  /**
   * Registriert sich mit SPL Autoload
   * 
   */
  public static function register() {
    if (function_exists('__autoload')) {
      throw new Exception('konservatives __autoload verhindert das Laden dieses autoloaders');
    }
    spl_autoload_register(array('\Psc\AutoLoader','autoLoad'));
  }
  

  public static function init() {
    define('PSC_AUTOLOADER_COPY',FALSE);
    // src/psc/class/Psc
    define('SRC_PATH',realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.str_repeat('..'.DIRECTORY_SEPARATOR,3)).DIRECTORY_SEPARATOR);  
    
    self::$path = SRC_PATH.'psc'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR;
    self::register();
  }
}