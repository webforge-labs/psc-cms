<?php

class Config extends Object {
  
  protected static $loaded = FALSE;

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
    if (mb_strpos($args[0],'.') !== FALSE)
      $args = $keyParts = explode('.',$key);
    else
      $keyParts = $args;

    $array = self::getVars();
    while (count($keyParts) > 0) {
      $k = array_shift($keyParts);

      if (array_key_exists($k, $array)) {
        $array = $array[$k];
      } else
        throw new Exception('Config-Variable: $GLOBALS[\'conf\']'.A::join($args, "['%s']").' nicht gefunden');
    }
    
    return $array;
  }

  public static function getVars() {
    if (!self::$loaded) {
      foreach (PSC::getLoadedModules() as $module) {
        require_once $module.DIRECTORY_SEPARATOR.'inc.config.php';
      }
      self::$loaded = TRUE;
    }

    return (array) @$GLOBALS['conf'];
  }
}

?>