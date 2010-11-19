<?php

/**
 * Driver Klasse
 * 
 * Falls Kohana nicht vorhanden ist
 */
if (!class_exists('utf8')) {

  /* load utf8 library */
  require_once dirname(__FILE__).'/utf8/utf8.php';
  
  class utf8 {
    
    // ab php 5.3.0 geht das hier:
    public static function __callStatic($function, $args) {
      
      if (!function_exists('utf8_'.$function))
        throw new Exception('Funktion '.$function.' ist im utf8 Paket nicht enthalten. (Cannot find utf8_'.$function.')');
        
      return call_user_func_array($function,$args);
    }
  }
}

?>