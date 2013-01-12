<?php

namespace Psc\CMS\Ajax;

use \stdClass,
    Psc\Code\Code
  ;

class ExceptionResponse extends StandardResponse {
  
  protected $exception;
  
  public function __construct(\Exception $e) {
    $this->data = new stdClass();
    $this->data->status = Response::STATUS_FAILURE;
    
    $this->setContent(\Psc\Exception::getExceptionText($e, $format = 'text'));
    $this->setContentType(Response::CONTENT_TYPE_JSON);
    
    $this->exception = $e;
  }
}

?>