<?php

namespace Psc;

class MissingEnvironmentVariableException extends \Psc\Exception {
  
  public $name;
  
  public $currentValue;
  
  public static function factory($name, $message, $code = 0) {
    $currentValue = getenv($name);
    $e = new static(
      sprintf("Environment-Variable: '%s' konnte nicht ausgelesen werden: %s. Ausgelesener Wert ist: '%s'", $name, $message, $currentValue),
      $code
    );
    $e->name = $name;
    $e->currentValue = $currentValue;
    throw $e;
  }
  
}
?>