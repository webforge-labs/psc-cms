<?php

class Environment extends Object {
  
  const WINDOWS = 'windows';
  const UNIX = 'unix';


  protected $os = NULL;
  protected $queryString = NULL;
  protected $http = NULL;
  protected $https = NULL;
  
  /**
   * Dieser Konstruktor sollte nie normal benutzt werden stattdessen PSC::getEnvironment() benutzen
   * 
   * Beim Erstellen des Objektes werden alle Werte mit bekannten Methoden gesetzt. Diese können dann nötigenfalls
   * durch PSC::getEnvironment überschrieben werden, denn da wird die Instanz erzeugt
   */
  public function __construct() {
    /* OS finden */
    if (substr(PHP_OS, 0, 3) == 'WIN') {
      $this->os = self::WINDOWS;
    } else {
      $this->os = self::UNIX;
    }

    /* QueryString */
    $qs = (string) $_SERVER['QUERY_STRING'];
    if (mb_strpos($qs,'?') === 0)
      $qs = mb_substr($qs,1);
    $this->queryString = ($qs == '') ? NULL : $gs;
    
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
  
  
  /**
   * Gibt den Query String des aktuellen Scripts zurück 
   * 
   * @return string Der Query String OHNE das vorangehende Fragezeichen
   */
  public static function getQueryString() {
    return $this->queryString;
  }


  /**
   * Überprüft ob das PHP Skript sich in einer HTTP Umgebung befindet (normal Fall)
   * @return bool
   */
  public static function isHTTP() {
    return $this->http;
    
  }

  /**
   * Überprüft ob HTTPS eingeschaltet ist
   * @return bool
   */  
  public static function isHTTPS() {
    return $this->https;
  }

  /**
   * Fügt dem Include Path einen neuen Pfad hinzu
   * 
   * @param string $path der Pfad zum Verzeichnis als String
   * @param string $type append|prepend der Default ist prepend
   * @return Description
   */
  public static function addIncludePath($path, $type = 'prepend') {
    if ($type == 'append') {
      set_include_path(get_include_path().PATH_SEPARATOR.$path);
    } else {
      set_include_path($path.PATH_SEPARATOR.get_include_path());
    }
  }
}

?>