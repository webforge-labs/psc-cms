<?php

namespace Psc\UI;

class UI {
  
  static $namespace = 'psc-cms-ui-';
  
  /**
   * Fügt jeder Klasse des Tags einen namespace hinzu, sofern die klasse mit \Psc anfängt
   */
  public static function getClass($class) {
    
    if (mb_strpos($class,'\\Psc\\') === 0) {
      return self::$namespace.mb_substr($class,5);
    }

    return $class;                                  
  }
  
}

?>