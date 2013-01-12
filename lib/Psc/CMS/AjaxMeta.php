<?php

namespace Psc\CMS;

use Psc\Net\HTTP\Request;
use Psc\Code\Code;
use stdClass;

/**
 * 
 */
class AjaxMeta extends \Psc\SimpleObject {
  
  const GET = 'GET';
  
  const POST = 'POST';
  
  const DELETE = 'DELETE';
  
  const PUT = 'PUT';
  
  protected $method;
  
  protected $url;
  
  /**
   * @var object
   */
  protected $body;
  
  /**
   * @param const $method Psc\Net\HTTP\Request::GET|POST|PUT|DELETE
   */
  public function __construct($method, $url, stdClass $body = NULL) {
    $this->setMethod($method);
    $this->url = $url;
    $this->body = $body;
  }
  
  /**
   * @return string
   */
  public function getUrl() {
    return $this->url;
  }
  
  /**
   * @param string $url
   */
  public function setUrl($url) {
    $this->url = $url;
    return $this;
  }
  
  public function appendUrl($part) {
    $this->url .= $part;
    return $this;
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
  
  /**
   * @param object $body
   */
  public function setBody($body = NULL) {
    $this->body = $body;
    return $this;
  }
  
  /**
   * @return object
   */
  public function getBody() {
    return $this->body;
  }
}
?>