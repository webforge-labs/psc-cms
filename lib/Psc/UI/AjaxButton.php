<?php

namespace Psc\UI;

use Psc\CMS\RequestMeta;

/**
 * 
 */
class AjaxButton extends Button {
  
  /**
   * @var Psc\CMS\RequestMeta
   */
  protected $requestMeta;
  
  
  protected function doInit() {
    parent::doInit();
    
    $this->html->addClass('\Webforge\Common\ArrayUtiljax-button');
  }
  
  /**
   * @param Psc\CMS\RequestMeta $requestMeta
   */
  public function setRequestMeta(RequestMeta $requestMeta) {
    $this->requestMeta = $requestMeta;
    if (!isset($this->data))
      $this->data = array();
      
    $this->data['request'] = $this->requestMeta->export();
    return $this;
  }
  
  /**
   * @return Psc\CMS\RequestMeta
   */
  public function getRequestMeta() {
    return $this->requestMeta;
  }
}
?>