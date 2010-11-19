<?php

class PSC extends Object {
  /**
   * Geladene Module
   * 
   * psc ist immer geladen
   * @var array
   */
  protected static $modules = array('psc');
  
  proteted static $environment = NULL;
  
  public static function require_module($moduleName) {
    $moduleBootstrap = strtolower($moduleName).DIRECTORY_SEPARATOR.'bootstrap.php';
    /* 
     * if (!file_exists($moduleBootstrap))
     *   throw new Exception('Modul: '.$moduleName.' nicht gefunden. Das Verzeichnis muss im include_path liegen. ('.get_include_path().')');
     */

    require_once $moduleBootstrap;
    self::$modules[] = strtolower($moduleName);
  }

  public static function getLoadedModules() {
    return self::$modules;
  }

  /**
   * 
   * gibt eine Liste der Main-Verzeichnisse der Module die sich in src befinden zurück
   * @return Dir[]
   */
  public static function getAvaibleModules() {
    $modules = array();

    $src = new Dir(SRC_PATH);
    $src->ignores[] = '/^.svn$/';
    
    foreach ($src->getContents() as $srcItem) {
      if ($srcItem instanceof Dir) {
        $modules[$srcItem->getName()] = $srcItem;
      }
    }

    unset($modules['fluidgrid']);
    return $modules;
  }

  /**
   * Gibt den vollen Namen der Klasse aus der Datei zurück
   * 
   * dies parsed nicht den Code der Datei, sondern geht von der Klassen-Struktur wie in Konventionen beschrieben aus
   * @return string
   */
  public static function getFullClassName(File $classFile) {
    $pa = $classFile->getDirectory()->getPathArray();
    $package = array_pop($pa);

    if ($package == 'class')
      return $classFile->getName(File::WITHOUT_EXTENSION);
    else
      return $package.$classFile->getName(File::WITHOUT_EXTENSION);
  }
  
  
  public static function registerVendorModule($name) {
    return PSC::getEnvironment()->addIncludePath(SRC_PATH.$name);
  }

  /**
   * 
   * @return Environment
   */
  public static function getEnvironment() {
    if (!isset(self::$environment)) {
      self::$environment = new Environment();
    }
    
    return self::$environment;
  }
}

?>