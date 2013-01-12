<?php

namespace Psc\CMS\Ajax;

use \stdClass,
    Psc\Code\Code    
  ;

class UploadResponse extends StandardResponse {

  public function __construct($status = Response::STATUS_OK) {
    if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== FALSE)) {
      $contentType = Response::CONTENT_TYPE_JSON;
    } else {
      /* IE braucht mal wieder Streicheleinheiten für den Upload */
      $contentType = Response::CONTENT_TYPE_PLAIN;
    }
      
    parent::__construct($status, $contentType);
  }
  
  public function export() { // wir überschreiben das hier da standardresponse hier nach dem content-type guckt den wir ja faken
    return $this->JSON();
  }
}

  
  
