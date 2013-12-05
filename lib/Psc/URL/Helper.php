<?php

namespace Psc\URL;

class Helper {

  const RELATIVE = 0x000002;
  const ABSOLUTE = 0x000004;
  
  const ENV_QUERY_VARS = 0x000010;
  const MERGE_QUERY_VARS = 0x000020;
  const MY_QUERY_VARS = 0x000040;
  
  /**
   * Wandelt eine Relative in eine absolute URL um (hängt den Host davor)
   * @param string $section sollte immer mit / anfangen
   */
  protected static function absolute($relativeURL) {
    $protocol = 'http';
    $url = $protocol.'://'.rtrim(@$_SERVER['HTTP_HOST'],'/');
    
    $url .= $relativeURL;
    
    return $url;
  }
  
  /**
   * Gibt die aktuelle relative URL zurück
   *
   * @param bitmap $flags
   * @param Array $queryVars
   */
  public static function getRelative($flags = 0x000000, Array $queryVars = array()) {
    $flags |= self::RELATIVE;
    return self::getURL(NULL,$flags, $queryVars);
  }
  
  /*
   * Gibt die aktuelle absolute URL zurück
   *
   * Die Parameter verhalten sich so wie bei getRelative und getURL() außer, dass die zurückgegebene URL
   * eine Absolute ist
   */
  public static function getAbsolute($flags = 0x000000, Array $queryVars = array()) {
    $flags |= self::ABSOLUTE;
    return self::getURL(NULL,$flags,$queryVars);
  }
  
  /**
   * Konstruiert eine URL
   * 
   * @param string|NULL|\Psc\URL\Routable $section wenn $section NULL ist, wird der Teil dynamisch bestimmt (aktuelle Seite)
   */
  public static function getURL($section, $flags = 0x000000, Array $queryVars = array()) {
    /*
      Query Vars Handling
    */
    if ($section === NULL) {
      $url = $_SERVER['PHP_SELF']; // da bin ich mir hier noch nicht ganz sicher
      if (!isset($_SERVER['PHP_SELF'])) {
        throw new \Psc\Exception('SERVER[PHP_SELF] not set!');
      }
    } elseif ($section instanceof Routable) {
      /* Achtung hier return */
      return $section->getRoutingURL();
    } else {
      $url = $section;
    }
    
    if ($flags & self::ENV_QUERY_VARS) {
      $queryVars = (array) $_GET;
    } elseif($flags & self::MERGE_QUERY_VARS) {
      $queryVars = array_merge((array) $_GET, $queryVars);
    } elseif($flags & self::MY_QUERY_VARS) {
      $queryVars = (array) $queryVars;
    }
    $queryVars = array_filter($queryVars,create_function('$a', 'return $a != ""; '));
    
    if (count($queryVars) > 0) {
      if (mb_substr($url,-1) != '/' && mb_substr($url,-5) != '.html' && mb_substr($url,-4) != '.php')
        $url .= '/';

      $url .= '?';
      $url .= http_build_query($queryVars);
    }

    if ($flags & self::ABSOLUTE)
      $url = self::absolute($url);
      
    return $url;
  }
  
  public static function getCurrent() {
    return self::getRelative(\Psc\URL\Helper::MERGE_QUERY_VARS);
  }

  public static function redirect($location) {
    header('Location: '.$location);
    exit;
  }
  
  /**
   * @return bool
   */
  public static function isHTTPS($url) {
    return parse_url($url, PHP_URL_SCHEME) === 'https';
  }
}
  
?>