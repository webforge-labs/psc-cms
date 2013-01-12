<?php

namespace Psc;

class MissingPropertyException extends Exception {
  
  public $prop;
  
  public function __construct($prop, $message = '', $code = 0, Exception $previous = NULL) {
    parent::__construct($message, $code, $previous);
    
    $this->prop = $prop;

    foreach ($this->getTrace() as $trace) {
      if ($trace['function'] == '__get' &&
          $trace['type'] == '->' &&
          isset($trace['args'][0]) &&
          $trace['args'][0] == $this->prop) {
          $this->line = $trace['line'];
          $this->file = $trace['file'];
          
      }
    }
  }
}
?>