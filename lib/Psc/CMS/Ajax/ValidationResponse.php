<?php

namespace Psc\CMS\Ajax;

use \stdClass,
    Psc\Code\Code
  ;

class ValidationResponse extends ExceptionResponse {
  
  public function __construct(\Exception $e) {
    parent::__construct($e);
    
    $this->setContent($e->getMessage());
    $this->setContentType(Response::CONTENT_TYPE_JSON);
  }
}

?>