<?php

namespace Psc\MailChimp;

use Psc\URL\Request;
use Psc\URL\Response;
use Psc\URL\RequestDispatcher;

/**
 * 
 * @version 1.3
 */
class API extends \Psc\SimpleObject {
  
  /**
   * @var string
   */
  protected $apiKey;
  
  /**
   * 
   * http://<dc>.api.mailchimp.com/1.3/?output=OUTPUT_FORMAT&method=SOME-METHOD&[other parameters]
   * 
   * dc ist hier das datacenter
   * @var integer
   */
  protected $dataCenter;
  
  /**
   * @var string
   */
  protected $apiUrl = 'http://%s.api.mailchimp.com/1.3/?output=json&method=%s';
  
  /**
   * Der aktuelle Request
   */
  protected $request;
  
  /**
   * @var Psc\URL\RequestDispatcher
   */
  protected $service;
  
  public function __construct($apiKey, RequestDispatcher $service = NULL) {
    $this->setApiKey($apiKey);
    $this->setService($service ?: new RequestDispatcher());
  }
  
  /**
   * Subscribe the provided email to a list. By default this sends a confirmation email - you will not see new members until the link contained in it is clicked!
   * 
   * listSubscribe(string apikey, string id, string email_address, array merge_vars, string email_type, bool double_optin, bool update_existing, bool replace_interests, bool send_welcome)
   * http://apidocs.mailchimp.com/api/1.3/listsubscribe.func.php
   * 
   * @todo im Moment sind nur die ersten 2 parameter implementiert
   * @param emailType text|html|mobile
   * @return bool success
   */
  public function listSubscribe($listId, $email, Array $mergeVars = NULL, $emailType = 'text', $doubleOptin = true, $updateExisting = false, $replaceInterests = true, $sendWelcome = true) {
    $response = $this->dispatch(__FUNCTION__, array(
      'id'=>$listId,
      'email_address'=>$email,
      'email_type'=>$emailType
    ));
    
    // boolean
    if ($response->getRaw() === 'true') {
      return TRUE;
    } else {
      throw Exception::unknown($response->getRaw());
    }
  }
  
  public function dispatch($method, Array $parameters = array()) {
    $json = json_encode($this->buildParameters($parameters));
    $url = $this->buildApiUrl($method);
    
    $response = $this->service->dispatch(
      $this->request = $this->service->createRequest('POST', $url, $json, array('Content-Type'=>'application/json; charset=utf-8'))
    );
    
    return $this->processResponse($response, $this->request);
  }
  
  protected function processResponse(Response $response, Request $request) {
    if ($response->hasHeaderField('X-MailChimp-API-Error-Code')) {
      throw Exception::fromCode($response->getHeaderField('X-MailChimp-API-Error-Code'), $request);
    }
    
    return $response;
  }
  
  protected function buildApiUrl($method) {
    return sprintf($this->apiUrl, $this->dataCenter, $method);
  }
  
  protected function buildParameters(Array $parameters = array()) {
    $parameters['apikey'] = $this->apiKey;
    return $parameters;
  }
  
  /**
   * @param string $apiKey
   */
  public function setApiKey($apiKey) {
    $this->apiKey = $apiKey;
    $this->setDataCenterFromApiKey($apiKey);
    return $this;
  }
  
  /**
   * @return string
   */
  public function getApiKey() {
    return $this->apiKey;
  }
  
  /**
   * @param integer $dataCenter
   */
  public function setDataCenter($dataCenter) {
    $this->dataCenter = $dataCenter;
    return $this;
  }
  
  public function setDataCenterFromApiKey($key) {
    // d7f6ebf645b7245918595f48b5946f81-us5
    $this->dataCenter = mb_substr($key, mb_strrpos($key, '-')+1);
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getDataCenter() {
    return $this->dataCenter;
  }
  
  /**
   * @param Psc\URL\RequestDispatcher $service
   */
  public function setService(RequestDispatcher $service) {
    $this->service = $service;
    return $this;
  }
  
  /**
   * @return Psc\URL\RequestDispatcher
   */
  public function getService() {
    return $this->service;
  }
}
?>