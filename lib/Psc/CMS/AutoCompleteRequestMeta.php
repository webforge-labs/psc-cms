<?php

namespace Psc\CMS;

/**
 * 
 */
class AutoCompleteRequestMeta extends RequestMeta {
  
  /**
   * @var int
   */
  protected $minLength;
  
  /**
   * @var int in ms
   */
  protected $delay;
  
  /**
   * @var integer
   */
  protected $maxResults;
  
  /**
   * @param const $method Psc\Net\HTTP\Request::GET|POST|PUT|DELETE
   */
  public function __construct($method, $url, Array $inputMeta = array(), Array $concreteInput = NULL, $minLength = 2, $delay = 300, $maxResults = NULL) {
    parent::__construct($method, $url, $inputMeta, $concreteInput);
    $this->setMinLength($minLength);
    $this->setDelay($delay);
    if (isset($maxResults))
      $this->setMaxResults($maxResults);
  }
  
  /**
   * @param int $delay in ms
   * @chainable
   */
  public function setDelay($delay) {
    $this->delay = max(0,(int) $delay);
    return $this;
  }
  
  /**
   * @return int
   */
  public function getDelay() {
    return $this->delay;
  }
  
  /**
   * @param int $minLength
   * @chainable
   */
  public function setMinLength($minLength) {
    $this->minLength = max(0, (int) $minLength);
    return $this;
  }
  
  /**
   * @return int
   */
  public function getMinLength() {
    return $this->minLength;
  }
  
  /**
   * @param integer $maxResults
   */
  public function setMaxResults($maxResults) {
    $this->maxResults = max(1,$maxResults);
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getMaxResults() {
    return $this->maxResults;
  }
}
?>