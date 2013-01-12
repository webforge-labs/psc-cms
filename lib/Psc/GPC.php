<?php

namespace Psc;

class GPC extends GlobalInput {
  
  const SET = 'GPC::CONST_set';
  
  /**
   * In manchen harten Fällen (wordpress) ist get_magic_quotes_gpc() nicht verlässlich
   */
  public static $forceStripSlashes = false;
  
  /**
   * Gibt den Wert (oder Array) ohne magic Slashes zurück
   * 
   * @param array|string $value
   */  
  public static function clean($value) {
    if (!get_magic_quotes_gpc() && !self::$forceStripSlashes) return $value;
    
    return is_array($value) ?
            array_map(array('GPC','clean'), $value) :
            stripslashes($value);
  }
}
?>