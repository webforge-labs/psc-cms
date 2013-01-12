<?php

namespace Psc\Code\Test;

use Psc\URL\Request;
use Psc\CMS\Configuration;
use Psc\URL\Response;
use Psc\CMS\Project;

/**
 * Konfiguriert einen Request für einen Self-Test (CMS / EntityService)
 */
class CMSRequestDispatcher extends \Psc\SimpleObject {
  
  /**
   * @var Psc\URL\Request
   */
  protected $request;
  
  /**
   * @var Psc\CMS\Configuration
   */
  protected $hostConfig;
  
  /**
   * @var Psc\CMS\Project
   */
  protected $project;
  
  /**
   * @var Psc\URL\Response
   */
  protected $response;
  
  protected $url;
  protected $method;
  
  protected $publicRequest = FALSE; // wenn true wird nicht cms.xxx als url genommen
  
  public $sendDebugSessionCookie = FALSE;

  public function __construct($method, $url, Configuration $hostConfig = NULL, Project $project = NULL) {
    $this->url = $url;
    $this->method = $method;
    $this->project = $project ?: \Psc\PSC::getProject();
    $this->hostConfig = $hostConfig ?: $this->project->getHostConfig();
  }
  
  /**
   * @return Psc\URL\Response
   */
  public function dispatch() {
    $this->getRequest()->init()->process();
    
    return $this->response = $this->getRequest()->getResponse();
  }
  
  /**
   * @param string $contentType
   */
  public function setContentType($contentType) {
    $curl = $this->getRequest();
    if (mb_strtolower($contentType) === 'html') {
      $curl->setHeaderField('Accept', 'text/html');
    } elseif (mb_strtolower($contentType) === 'json') {
      $curl->setHeaderField('Accept', 'application/json');
    } else {
      $curl->setHeaderField('Accept', $contentType);
    }
    return $this;
  }
  
  public function setHeaderField($name, $value) {
    $this->getRequest()->setHeaderField($name, $value);
    return $this;
  }

  public function setHeaderFields(Array $headers) {
    foreach ($headers as $name => $value) {
      $this->getRequest()->setHeaderField($name, $value);
    }
    return $this;
  }
  
  /**
   * @return Psc\URL\Request
   */
  public function getRequest() {
    if (!isset($this->request)) {
      $this->request = new \Psc\URL\Request($this->expandUrl($this->url));
      $this->request->setAuthentication($this->hostConfig->req('cms.user'),$this->hostConfig->req('cms.password'),CURLAUTH_BASIC);
      $this->request->setHeaderField('X-Psc-Cms-Connection','tests');
      $this->request->setHeaderField('X-Psc-Cms-Debug-Level',15);
      
      if ($this->method == 'GET' || $this->method == 'POST') {
        $this->request->setType($this->method);
      } else {
        $this->request->setType('POST');
        $this->request->setHeaderField('X-Psc-Cms-Request-Method',$this->method);
      }
      
      $this->setContentType('html');
    
      // this is great for debugging, but it slows down 50%
      if ($this->sendDebugSessionCookie && $this->hostConfig->get('uagent-key') != NULL) {
        $this->request->setHeaderField('Cookie', 'XDEBUG_SESSION='.$this->hostConfig->get('uagent-key'));
      }
    }
    return $this->request;
  }
  
  public function removeAuthentication() {
    $this->getRequest()->removeAuthentication();
    return $this;
  }
  
  protected function expandUrl($url) {
    if ($this->publicRequest) {
      $baseUrl = $this->project->getBaseURL();
    } else {
      $baseUrl = $this->project->getCMSBaseURL();
    }
    
    return $baseUrl.ltrim($url,'/');
  }
  
  public function resetRequest() {
    $this->request = NULL;
    return $this;
  }
  
  /**
   * @param Psc\CMS\Configuration $hostConfig
   */
  public function setHostConfig(Configuration $hostConfig) {
    $this->hostConfig = $hostConfig;
    return $this;
  }
  
  /**
   * @return Psc\CMS\Configuration
   */
  public function getHostConfig() {
    return $this->hostConfig;
  }
  
  /**
   * @return Psc\URL\Response
   */
  public function getResponse() {
    return $this->response;
  }
  
    /**
   * @param bool $publicRequest
   * @chainable
   */
  public function setPublicRequest($publicRequest) {
    $this->publicRequest = $publicRequest;
    return $this;
  }

  /**
   * @return bool
   */
  public function getPublicRequest() {
    return $this->publicRequest;
  }
}
?>