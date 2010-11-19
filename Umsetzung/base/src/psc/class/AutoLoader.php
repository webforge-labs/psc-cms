<?php

class AutoLoader {
  
  public static $path = SRC_PATH;

  /**
   * Dateien die schon geladen wurden
   * @var array
   */
  protected static $files = array();
  
  /**
   * Hilfsfunktion für den Autoload mit SPL
   * 
   */
  public static function autoLoad($class) {
    /* in dieser funktion nur pures php verwenden */
    

    /* klasse bereits geladen? */
    if (in_array($class, self::$files) || class_exists($class, false) || interface_exists($class, false))
      return;

    /* modul herausfinden */
    $modules = array();

    if (($us = strpos($class,'_')) !== FALSE) {
      $modules[] = strtolower(substr($class,0,$us));
      $class = substr($class,$us+1);
    }
    $modules[] = 'psc';
    $modules[] = NULL; // bedeutet im src path suchen

    $nameSpace = self::getNameSpace($class);

    foreach ($modules as $module) {
      
      if (isset($nameSpace)) {
        try {
          /* klasse in namespace-unterverzeichnis (ohne namespace als prefix)  */
          $path = self::getPath('class', array($nameSpace, substr($class,strlen($nameSpace))), $module); // namespace vom klassennamen entfernen für den dateinamen

          break;
        } catch(AutoLoaderPathException $e) {
          $path = NULL;
        }
      }

      if (!isset($path)) {
        try {
          /* klasse ohne namespace-unterverzeichnis */
          $path = self::getPath('class', $class, $module);

          break;
        } catch (AutoLoaderPathException $e) {
          
        }
      }
    }

    /* wir haben keine Klasse zum laden gefunden */
    if (!isset($path))
      return FALSE;

    require_once $path;
    self::$files[] = $class;
  }



  /**
   * Gibt den Pfad zu einer Datei zurück
   * 
   * Ist kein ModulName angegeben wird im src path gesucht.
   * ist $file ein array wird der letzte bestandteil als dateiname interpretiert
   * $file hat nie die endung php
   * <code>
   * getPath('ctrl', 'home/article');
   * getPath('view', 'home/welcome');
   * </code>
   * @param string $fileNamespace
   * @param string|array $file wenn ein array, dann wird dies als pfad zur datei interpretiert. $file kann aber ebenso ein string mit / (oder mehreren) sein (niemals mit \ !)
   * @param string der Name des Modules
   * @return string|FALSE der Pfad zur Datei mit \ oder / je nach Betriebssystem
   * @exception wenn die Datei nicht gefunden werden kann
   */
  public static function getPath($fileNamespace, $file, $module = NULL) {
    /* für das Format der Pfade in konventionen.txt nachschauen */

    $path = self::$path;
 
    if (isset($module)) {
      /* 
         wir lassen hier den check, ob ein modul existiert, erst einmal weg
         und untersuchen das lieber bei der Exception außerhalb dieser klasse
      */
      $path .= $module.DIRECTORY_SEPARATOR;
    } /* ansonsten gibt es kein modul und wir suchen die datei im src path */
    
    if (!is_array($file)) {
      $file = explode('/',$file);
    }

    $filename = array_pop($file);
    $directory = implode(DIRECTORY_SEPARATOR,$file);

    if ($directory != '')
      $directory .= DIRECTORY_SEPARATOR;


    /* namespace in eigenem Verzeichnis:
       src/ctrl/home/article.php 
    */
    $path1 = $path.$fileNamespace.DIRECTORY_SEPARATOR.$directory.$filename.'.php';

    if (self::is_file($path1))
      return $path1;

    /* namespace vor der Datei:
       src/home/ctrl.article.php 
    */
    $path2 = $path.$directory.$fileNamespace.'.'.$filename.'.php';

    if (self::is_file($path2))
      return $path2;

    
    $e = new AutoLoaderPathException('Pfad zur Resource: fileNamespace: '.$fileNamespace.' Verzeichnis: '.$directory.' Datei: '.$filename.' nicht gefunden!');
    $e->paths[] = $path1;
    $e->paths[] = $path2;
    throw $e;
  }


  /**
   * Gibt den möglichen NameSpace eines CamelCase Klassen-Namen zurück
   * 
   * HTMLElement => HTML
   * TableColumn => Table
   * IObjectModel => Object
   */
  protected static function getNameSpace($camelCaseWord) {
    /* 
     * $camelCaseWord = 'Object';
     * $camelCaseWord = 'ObjectModel';
     * $camelCaseWord = 'IObjectModel';
     * $camelCaseWord = 'HTMLElement';
     * $camelCaseWord = 'IHTMLElement';
     * $camelCaseWord = 'IInterfaceModel';
     * $camelCaseWord = 'DBConnection';
     * $camelCaseWord = 'DBMySQLConnection';
     */
    $nameSpace = NULL;
    if (preg_match('/^I?(([A-Z][a-z]+)[A-Z].+|([A-Z]+)[A-Z][a-z])/u',$camelCaseWord,$match) > 0) {
      $nameSpace = array_pop($match);
    }

    return $nameSpace;
  }


  protected static function is_file($file) {
    //print "Ueberpruefe: ".$file."<br />\n";
    return is_file($file);
  }

  /**
   * Registriert sich mit SPL Autoload
   * 
   */
  public static function register() {
    spl_autoload_register(array('AutoLoader','autoLoad'));
  }
}


class AutoLoaderPathException extends Exception {
  public $paths = array();
}