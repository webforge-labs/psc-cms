<?php

namespace Psc\URL;

/**
 * das RequestBundle ist leider zu sehr aufs Caching konzentriert. (und macht des auch noch falsch)
 * Diese Klasse soll sehr simple Möglichkeit sein eine DPI zu Request und Response (z. B. für APIs) auflösen zu können
 */
class RequestDispatcher extends \Psc\SimpleObject {
  
  /**
   * @var array list($user, $password, $type)
   */
  protected $authentication;
  
  /**
   * A fully optional response reader for the read() function
   */
  protected $responseReader;
  
  /**
   * @var Psc\URL\Response
   */
  protected $lastResponse;
  
  public function __construct(ResponseReader $reader = NULL) {
    $this->responseReader = $reader;
  }
  
  public function dispatch(Request $request) {
    if (!$request->isInit()) {
      $request->init();
    }
    
    $request->process();
    
    return $this->lastResponse = $request->getResponse();
  }

  public function createRequest($method, $url, $body = NULL, Array $headers = array()) {
    $request = new Request($url);
    
    if ($method === 'GET') {
      $request->setType($method);
    } else {
      $request->setType('POST');
      
      if ($method !== 'POST') {
        $headers['X-HTTP-METHOD-OVERRIDE'] = $method;
      }
    }
    
    if (isset($this->authentication)) {
      list ($user, $password, $type) = $this->authentication;
      $request->setAuthentication($user, $password, $type);
    }
    
    if ($body)
      $request->setData($body);
    
    foreach ($headers as $name => $value) {
      $request->setHeaderField($name, $value);
    }
    
    return $request;
  }

  public function read($expectedFormat, $expectedCode = 200) {
    if (!isset($this->lastResponse)) {
      throw new \RuntimeException('There is no last response to read');
    }
    
    return $this->getResponseReader()->read($this->lastResponse, $expectedFormat, $expectedCode);
  }
  
  public function setAuthentication($user, $password, $type = CURLAUTH_BASIC) {
    $this->authentication = array($user, $password, $type);
    return $this;
  }
  
  public function getResponseReader() {
    if (!isset($this->responseReader)) {
      $this->responseReader = new \Psc\URL\ResponseReader();
    }
    
    return $this->responseReader;
  }

  /**
   * @return Psc\URL\Response
   */
  public function getLastResponse() {
    return $this->lastResponse;
  }
}
?>