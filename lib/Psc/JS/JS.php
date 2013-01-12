<?php

namespace Psc\JS;

use \Webforge\Common\System\File,
    \Psc\System\System,
    \Psc\PSC
;


class JS {
  
  /**
   * Fügt dem Standard JS Manager eine bekannte JS Datei hinzu
   */
  public static function enqueue($alias) {
    return self::getManager()->enqueue($alias);
  }
  
  /**
   * Macht dem Standard JS Manager eine JS Datei bekannt
   */
  public static function register($jsFile, $alias = NULL, Array $dependencies = array()) {
    return self::getManager()->register($jsFile, $alias, $dependencies);
  }
  
  /**
   * Löscht aus dem Standard JS Manager eine bekannte JS Datei
   */
  public static function unregister($alias) {
    return self::getManager()->unregister($alias);
  }
  
  /**
   * Sagt dem JS Manager, dass er sich darum nicht mehr kümmern muss
   */
  public static function ignore($alias) {
    return self::getManager()->ignore($alias);
  }


  public static function load() {
    return self::getManager()->load();
  }
  
  /**
   * @return JSManager
   */
  public static function getManager() {
    return Manager::instance('default');
  }
  
  
  public static function minify(\Webforge\Common\System\File $file) {
    try {
      $jar = System::which('java').' -jar "./yuicompressor-2.4.5.jar"';
      $bin = PSC::get(PSC::PATH_BIN);
      
      $target = clone $file;
      $target = $target->setExtension('min.js');
      
      try {
        $target->delete();
      } catch (\Webforge\Common\System\Exception $e) {}
    
      $cmd = $jar.' '.$file.' -o '.$target;
      System::execute($cmd,$bin);
      
      if (!$target->exists()) {
        throw new \Psc\Exception('Befehl wurde aufgerufen, aber target nicht geschrieben');
      }
      return $target;
      
    } catch (\Psc\Exception $e) {
      return $file;
    }
  }
}
?>