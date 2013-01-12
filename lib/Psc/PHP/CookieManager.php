<?php

namespace Psc\PHP;

class CookieManager {
  
  protected $domain;
  
  public function set($cookiename, $value) {
	$value_coded = base64_encode(serialize($value));
	return setcookie ($cookiename,$value_coded,(time()+24*60*60*365),'/',$this->getDomain(),0);
  }

  public function get($cookiename) {
    if (!isset($_COOKIE[$cookiename])) return NULL;
    $value_encoded = unserialize(base64_decode($_COOKIE[$cookiename]));
  	return $value_encoded;
  }
  
  public function del($cookiename) {
	return setcookie ($cookiename,'',time() - 3600,'/',$this->getDomain(),0);
  }
  
  /**
   * @param string $domain
   * @chainable
   */
  public function setDomain($domain) {
    $this->domain = $domain;
    return $this;
  }

  /**
   * @return string
   */
  public function getDomain() {
    return $this->domain;
  }
}
?>