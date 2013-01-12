<?php

namespace Psc\ICTS;

use \Psc\DataInput;

class Data extends \Psc\DataInput {
  
  const THROW_EXCEPTION = \Psc\DataInput::THROW_EXCEPTION;
  
  public function clean($value) {
    if(is_string($value) && trim($value) == '') {
      $value = NULL;
    }
    return $value;
  }
}