<?php

namespace Psc\Net\HTTP;

class SimpleURL extends \Psc\Object {
  
  const HTTP = 'http';
  const HTTPS = 'https';
  
  /**
   * Der Cache für die URL
   *
   * (muss nicht unbedingt aktuell sein)
   * @var string
   */
  protected $url;
  
  /**
   * @var string
   */
  protected $scheme = self::HTTP;

  /**
   * @var array
   */
  protected $hostParts = array();
  
  protected $port;
  protected $user;
  protected $password;
  
  /**
   * @var array
   */
  protected $path = array();
  
  protected $pathTrailingSlash = FALSE;

  /**
   * @var array
   */
  protected $query = array();
  
  
  /**
   * @var string ohne # davor
   */
  protected $fragment;
  
  public function __construct($url = NULL) {
    $this->url = $url;
    
    if (isset($url)) {
      $this->parseFrom($this->url);
    }
  }
  
  /**
   * @param string $url
   */
  public function parseFrom($url) {
    $url = (string) trim($url);
    
    $info = (object) parse_url($url);
    
    /* einfache Eigenschaften kopieren */
    foreach (array('port','user','pass','fragment','scheme') as $simple) {
      if (isset($info->$simple)) {
        $prop = $simple === 'pass' ? 'password' : $simple;
        $value = $info->$simple !== '' ? $info->$simple : NULL;
      
        $this->$prop = $value;
      } // nicht überschreiben wenn es nicht im array gesetzt ist
    }
    
    if (isset($info->host) && $info->host !== '') {
      $this->setHost($info->host);
    } else {
      throw new \Psc\Exception(sprintf("Kann keine URL aus '%s' parsen.", $url));
    }
    
    if (isset($info->query) && $info->query !== '') {
      parse_str($info->query, $this->query);
    }
    
    if (isset($info->path) && $info->path !== '') {
      //@TODO müssen wir hier noch url decodieren?
      $this->pathTrailingSlash = \Psc\String::endsWith($info->path,'/');
      $this->path = array_filter(array_map('trim',explode('/',trim($info->path,'/'))));
    }
    return $this;
  }
  
  public function toString() {
    
    $url  = $this->getScheme().'://';
    
    // user:password@ | user@
    if (isset($this->user)) {
      $url .= $this->user;
      if (isset($this->password))
        $url .= $this->password.':';
      $url .= '@';
    }
    
    $url .= $this->getHost();
    
    if (isset($this->port))
      $url .= ':'.$this->port;
      
    $url .= '/'; // schließt immer mit slash nach dem host ab
    
    if (count($this->path) > 0) {
      $url .= $this->getPathString();
    }
    
    if (count($this->query) > 0) {
      $url .= '?'.$this->getQueryString();
    }
    
    if ($this->fragment) {
      $url .= '#'.$this->fragment;
    }
    
    return $url;
  }
  
  /**
   * Gibt den Namen des Hosts zurück
   * Beispiel:
   *
   * tiptoi.philipp.zpintern
   * 127.0.0.1
   * 
   * @return string ohne http:// davor und / dahinter
   */
  public function getHost() {
    return implode('.',$this->hostParts);
  }
  
  /**
   * @param string $host
   */
  public function setHost($host) {
    $this->hostParts = explode('.',$host);
    return $this;
  }
  
  public function setHostParts(Array $parts) {
    $this->hostParts = $parts;
    return $this;
  }
  
  /**
   * @return SimpleURL
   */
  public function getHostURL() {
    $url = new self();
    $url->setScheme($this->scheme);
    $url->setHostParts($this->hostParts);
    $url->setPort($this->port);
    $url->setUser($this->user);
    $url->setPassword($this->password);
    
    // query, fragment und part natürlich nicht
    
    return $url;
  }
  
  /**
   * Gibt den Name des Hosts nach . getrennt zurück
   *
   * @return array
   */
  public function getHostParts() {
    return $this->hostParts;
  }
  
  /**
   * @return array
   */
  public function getPath() {
    return $this->path;
  }
  
  public function setPath(Array $parts) {
    $this->pathTrailingSlash = FALSE;
    $this->path = $parts;
    return $this;
  }
  
  public function addPathPart($string) {
    $this->path[] = $string;
    return $this;
  }
  
  /**
   * Gibt einen bestimten Teil des Paths zurück
   * 
   * Gibt NULL zurück wenn der Part nicht gesetzt ist
   * @param int $num 1-basierend
   * @return string|NULL
   */
  public function getPathPart($num) {
    if ($num < 1) throw new \InvalidArgumentException('Num ist 1-basierend');
    if ($num > count($this->path)) return NULL;
    
    return $this->path[$num-1];
  }
  
  /**
   * @return string ohne / davor und mit optionalem / dahinter (wenn pathTrailingSlash === TRUE ist)
   */
  public function getPathString() {
    if (count($this->path) === 0) return NULL;
    
    $s = implode('/',$this->path); // @TODO müssen wir hier noch url encodieren?
    if ($this->pathTrailingSlash)
      $s .= '/';
    
    return $s;
  }
  
  /**
   * @return string ohne ? davor
   */
  public function getQueryString() {
    return http_build_query($this->query, NULL, '&');
  }
  
  /**
   * @return bool
   */
  public function isHTTP() {
    return $this->scheme === self::HTTP;
  }

  /**
   * @return bool
   */
  public function isHTTPs() {
    return $this->scheme === self::HTTPS;
  }
  
  public function __toString() {
    try {
      return (string) $this->toString();
    } catch (\Exception $e) {
      return '[Class cannot converted to string because of ERROR: '.$e->getMessage().']';
    }
  }
}
?>