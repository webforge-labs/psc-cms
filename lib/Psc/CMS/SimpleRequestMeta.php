<?php

namespace Psc\CMS;

use Psc\Code\Code;

class SimpleRequestMeta implements RequestMetaInterface {
  
  /**
   * @var string
   */
  protected $url;
  
  /**
   * @var const
   */
  protected $method;
  
  public function __construct($method, $url) {
    $this->url = $url;
    $this->setMethod($method);
  }
  
  /**
   * @retun strdClass
   */
  public function export() {
    return (object) array(
      'method'=>$this->method,
      'url'=>$this->getUrl(),
      'body'=>$this->getBody()
    );
  }
  
  /**
   * @return string
   */
  public function getUrl() {
    return $this->url;
  }
  
  /**
   * @param const $method
   * @chainable
   */
  protected function setMethod($method) {
    Code::value($method, self::GET, self::POST, self::PUT, self::DELETE);
    $this->method = $method;
    return $this;
  }

  /**
   * @return const
   */
  public function getMethod() {
    return $this->method;
  }
}
?>