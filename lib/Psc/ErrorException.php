<?php

namespace Psc;

class ErrorException extends \Psc\Exception {
  
  protected $severity;
  
  public function __construct($message, $code, $severity, $filename, $line) {
    parent::__construct($message, $code);
    $this->severity = $severity;
    $this->file = $filename;
    $this->line = $line;
  }
  
  public function getSeverity() {
    return $this->severity;
  }
}
?>