<?php

namespace Psc\CMS\Service\Response;

class ExceptionResponse extends \Psc\CMS\Service\Response\Response {
  
  public function __construct(\Exception $e) {
    parent::__construct(\Psc\Net\Service::ERROR, $e);
  }
}
?>