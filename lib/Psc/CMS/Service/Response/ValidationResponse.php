<?php

namespace Psc\CMS\Service\Response;

class ValidationResponse extends \Psc\CMS\Service\Response\Response {

  public function __construct(\Psc\Form\ValidatorException $e) {
    parent::__construct(\Psc\Net\Service::ERROR, $e);
  }
}
?>