<?php

namespace Psc\CMS\Ajax;

use \stdClass,
    Psc\Code\Code,
    \Psc\JS\JSON
  ;

class StandardResponse extends \Psc\Object implements Response, JSON {
  
  /**
   * @var stdClass
   */
  protected $data = NULL;
  
  protected $contentType = NULL;
  
  protected $type = Response::TYPE_STANDARD;
  
  public function __construct($status = Response::STATUS_OK, $contentType = Response::CONTENT_TYPE_JSON) {
    if (\Psc\PSC::getProject()->isDevelopment()) {
      throw new \Webforge\Common\DeprecatedException('Dont use ajax responses anymore');
    }
    $this->data = new stdClass();
    $this->data->status = $status;
    $this->data->content = new stdClass();
    
    $this->setContentType($contentType);
  }
  
  public function setStatus($status) {
    Code::value($status, Response::STATUS_OK,Response::STATUS_FAILURE);

    $this->data->status = $status;
    return $this;
  }

  public function setType($type) {
    Code::value($type, Response::TYPE_VALIDATION, Response::TYPE_STANDARD);

    $this->data->type = $type;
    return $this;
  }
  
  public function getType() {
    return $this->data->type;
  }
  
  public function setContent($content) {
    $this->data->content = $content;
  }
  
  public function getContent() {
    return $this->data->content;
  }
  
  public function JSON() {
    $json = json_encode($this->data);
    
    if (\Psc\PSC::getProject()->isDevelopment()) {
      $json = \Psc\JS\Helper::reformatJSON($json);
    }
    
    return $json;
  }
  
  public function export() {
    if ($this->contentType == Response::CONTENT_TYPE_JSON) {
      return $this->JSON();
    }
    
    if ($this->contentType == Response::CONTENT_TYPE_HTML) {
      return $this->data->content;
    }
    
    throw new Exception('Kein bekannter contentType gesetzt, export() nicht möglich');
  }
  
  public function __toString() {
    try {
      return $this->export();
    } catch (\Exception $e) {
      print $e;
      exit;
    }
  }
  
  public function getStatus() {
    return $this->data->status;
  }
  
  public function getContentType() {
    return $this->contentType;
  }
  
  public function setContentType($contentType) {
    $this->contentType = $contentType;
  }
}
