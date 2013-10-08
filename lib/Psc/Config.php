<?php

namespace Psc;

use \Psc\Code\Code;

/**
 * Get nehmen wenn man eine Option checken will
 *
 * req nehmen wenn man eine Option unbedingt braucht
 * 
 * @deprecated use the configuration locally or get it from your project with the cms container
 */
class Config extends \Psc\Object {
  
  const NEW_ARRAY = 'array()';
  
  /**
   * 
   * Config::get('key1','key2','key3');
   * oder
   * Config::get('key1.key2.key3');
   * @param string $key Array indizes können mit . getrennt werden
   * @return mixed
   */
  public static function get($key) {
    $args = func_get_args();
    
    if (count($args) > 1) 
      return PSC::getProject()->getConfiguration()->get($args);
    else
      return PSC::getProject()->getConfiguration()->get($key);
  }
  
  /**
   * 
   * Config::require('key1','key2','key3');
   * oder
   * Config::require('key1.key2.key3');
   * @param string $key Array indizes können mit . getrennt werden
   * @exception wenn es den Config eintrag nicht gibt
   * @return mixed
   */
  public static function req($key) {
    $args = func_get_args();
    
    if (count($args) > 1) 
      return PSC::getProject()->getConfiguration()->req($args);
    else
      return PSC::getProject()->getConfiguration()->req($key);
    
    return $array;
  }
  
  /**
   * Required eine Variable, schlägt aber einen Default für die Config-Variable vor
   * 
   */
  public static function reqDefault($keys, $defaultValue) {
    try {
      $value = self::req($keys);
    } catch (ConfigMissingVariableException $e) {
      
      if (is_string($defaultValue) && !Preg::match($defaultValue,'/^(\'|")/') && !self::NEW_ARRAY) {
        $defaultValue = "'".$defaultValue."'";
      }
      
      $e->setDefault($defaultValue);
      throw $e;
    }
    
    return $value;
  }


  /**
   * Required eine Variable, schlägt aber einen Default für die Config-Variable vor
   * 
   */
  public static function getDefault($keys, $defaultValue) {
    try {
      $value = self::req($keys);
    } catch (ConfigMissingVariableException $e) {
      return $defaultValue;
    }
    
    return $value;
  }
}
?>