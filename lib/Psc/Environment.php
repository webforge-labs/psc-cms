<?php

namespace Psc;

use \Webforge\Common\System\Dir,
    \Psc\Code\ErrorHandler,
    \Psc\URL\Helper as URLHelper
;

class Environment extends \Psc\Object {
  
  const WINDOWS = 'windows';
  const UNIX = 'unix';
  
  protected $os = NULL;
  protected $queryString = NULL;
  protected $http = NULL;
  protected $https = NULL;
  
  protected $errorHandler;
  
  protected $requestUri;
  
  /**
   * Dieser Konstruktor sollte nie normal benutzt werden stattdessen PSC::getEnvironment() benutzen
   * 
   * Beim Erstellen des Objektes werden alle Werte mit bekannten Methoden gesetzt. Diese können dann nötigenfalls
   * durch PSC::getEnvironment überschrieben werden, denn da wird die Instanz erzeugt
   */
  public function __construct(ErrorHandler $errorHandler = NULL) {
    if (!isset($errorHandler)) {
      $errorHandler = new ErrorHandler();
    }
    $this->errorHandler = $errorHandler;
    
    /* OS finden */
    if (substr(PHP_OS, 0, 3) == 'WIN') {
      $this->os = self::WINDOWS;
    } else {
      $this->os = self::UNIX;
    }
    
    /* timezone */
    date_default_timezone_set('Europe/Berlin');

    /* QueryString */
    $qs = (string) @$_SERVER['QUERY_STRING'];
    if (mb_strpos($qs,'?') === 0)
      $qs = mb_substr($qs,1);
    $this->queryString = ($qs == '') ? NULL : $qs;
    
    /* HTTP / HTTPS */
    $this->http = (isset($_SERVER['SERVER_PROTOCOL']) && mb_strpos(mb_strtoupper($_SERVER['SERVER_PROTOCOL']),'HTTP') === 0);
    $this->https = (isset($_SERVER['HTTPS']) && mb_strtolower($_SERVER['HTTPS']) == 'on');
  }
  
  /**
   * Gibt das aktuelle Betriebssystem zurück
   * 
   * @return const windows|unix 
   */
  public function getOS() {
    return $this->os;
  }
  
  public function isWindows() {
    return $this->os === self::WINDOWS;
  }
  
  
  /**
   * Gibt den Query String des aktuellen Scripts zurück 
   * 
   * @return string Der Query String OHNE das vorangehende Fragezeichen
   */
  public function getQueryString() {
    return $this->queryString;
  }


  /**
   * Überprüft ob das PHP Skript sich in einer HTTP Umgebung befindet (normal Fall)
   * @return bool
   */
  public function isHTTP() {
    return $this->http;
    
  }

  /**
   * Überprüft ob HTTPS eingeschaltet ist
   * @return bool
   */  
  public function isHTTPS() {
    return $this->https;
  }

  /**
   * Fügt dem Include Path einen neuen Pfad hinzu
   * 
   * @param string $path der Pfad zum Verzeichnis als String
   * @param string $type append|prepend der Default ist prepend
   * @return Description
   */
  public function addIncludePath($path, $type = 'prepend') {
    if (!$this->hasIncludePath($path)) {
      if ($type == 'append') {
        $ret = set_include_path(rtrim(get_include_path(),PATH_SEPARATOR).PATH_SEPARATOR.$path);
      } else {
        $ret = set_include_path($path.PATH_SEPARATOR.ltrim(get_include_path(),PATH_SEPARATOR));
      }
      if ($ret === FALSE) {
        throw new \Psc\Exception('cannot set include path!. Vielleicht ist php_admin_value (statt php_value) auf include_path inder apache.conf gesetzt?');
      }
    }
    return $this;
  }
  
  /**
   * @return bool
   */
  public function hasIncludePath($path) {
    $paths = explode(PATH_SEPARATOR, get_include_path());
    return in_array($path,$paths);
  }
  
  public function getHostName() {
    if (PHP_SAPI === 'cli') {
      if (isset($_SERVER['HTTP_HOST']))
        return $_SERVER['HTTP_HOST'];
      else
        return php_uname('n');
        
    } else {

      if ($this->isHTTP() || $this->isHTTPS())
        return $_SERVER['HTTP_HOST'];
    
    }
  }
  
  /**
   * return array
   */
  public function getRequestPath() {
    $uri = $this->getRequestURI();
    if (\Webforge\Common\String::startsWith($uri, '/index.php'))
      $uri = mb_substr($uri,mb_strlen('index.php'));
    
    if (empty($uri) || $uri == '/') return array();
    
    $uri = ltrim($uri,'/');
    
    $parts = explode('/',$uri);
    return array_filter($parts, function ($p) {
      return (trim($p) != '');
    });
  }
  
  public function getRequestURI() {
    if (isset($this->requestUri)) {
      return $this->requestUri;
    }
    
    if (isset($_SERVER['REQUEST_URI'])) {
      $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
      $uri = rawurldecode($uri);
    } elseif (isset($_SERVER['REDIRECT_URL'])) {
      $uri = $_SERVER['REDIRECT_URL'];
    }
    
    return $uri;
  }
  
  public function setRequestURI($uri) {
    $this->requestUri = $uri;
    return $this;
  }
  
  /**
   * @param const $name PSC::PATH_* Konstante
   * @return \Webforge\Common\System\Dir Pfad ist aufgelöst und absolute
   * @deprecated
   */
  public static function getPath($name) {
    throw new Exception('deprecated');
  }
}

?>