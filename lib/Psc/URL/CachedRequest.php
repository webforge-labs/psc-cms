<?php

namespace Psc\URL;

class CachedRequest extends Request {
  
  protected $cachedResponse;
  
  public function setCachedResponse(Response $response) {
    $this->cachedResponse = $response;
    return $this;
  }
  
  public function setResponse(Response $response) {
    $this->cachedResponse = $response;
    return $this;
  }
  
  public function getCachedResponse() {
    return $this->cachedResponse;
  }
  
  public function process() {
    return $this->getCachedResponse()->getRaw();
  }

  public function getResponse() {
    return $this->cachedResponse;
  }
  
  public static function __set_state(Array $properties) {
    foreach (array('cachedResponse','url','cookieJar') as $p) {
      $this->$p = $properties[$p];
    }
  }
}

?>