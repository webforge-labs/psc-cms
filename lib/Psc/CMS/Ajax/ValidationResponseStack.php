<?php

namespace Psc\CMS\Ajax;

use \stdClass,
    Psc\Code\Code
  ;

class ValidationResponseStack extends ExceptionResponse {
  
  public function __construct(Array $exceptions) {
    $this->data = new stdClass();
    $this->setStatus(Response::STATUS_FAILURE);
    $this->setType(Response::TYPE_VALIDATION);
    
    $content = array();
    foreach ($exceptions as $e) {
      $error = array('message'=>$e->getMessage(),
                         'field'=>$e->field,
                         'data'=>$e->data
                        );
      
      if (isset($e->label)) {
        $error['label'] = $e->label;
      }
      
      $content[] = $error;
    }
    
    $this->setContent($content);
    $this->setContentType(Response::CONTENT_TYPE_JSON);
  }
}

?>