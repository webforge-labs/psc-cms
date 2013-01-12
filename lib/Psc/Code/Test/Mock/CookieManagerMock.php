<?php

namespace Psc\Code\Test\Mock;

class CookieManagerMock extends \Psc\PHP\CookieManager {
  
  protected $cookieJar;
  
  public function set($cookiename, $value) {
    $this->cookieJar[$cookiename] = $value;
  }
 
  public function get($cookiename) {
    return $this->cookieJar[$cookiename];
  }
   
  public function del($cookiename) {
    unset($this->cookieJar[$cookiename]);
    return TRUE;
  }
}

?>