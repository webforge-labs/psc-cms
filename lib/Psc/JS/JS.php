<?php

namespace Psc\JS;

/**
 * @deprecated
 */
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
}
