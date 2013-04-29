<?php

namespace Psc;

class ConfigMissingVariableException extends \Psc\Exception {
  
  public $phpCode;
  
  public $keys;

  public $default;
  
  public function __construct ($message = "", $code = 0, \Exception $previous = NULL) {
    if (is_array($message)) {
      $this->keys = $message;
     
      $ccode = '$conf'.A::join($this->keys, "['%s']");
      $message = sprintf(
        "Cannot read variable: '%s' from config. Put %s to your config.php",
        implode('.',$this->keys),
        $ccode
      );

      $this->phpCode = $ccode.' = NULL;';
    }
      
    parent::__construct($message, $code, $previous);
  }
  
  public function setDefault($default) {
    $this->default = $default;
    $this->phpCode = str_replace(' = NULL;',' = '.var_export($default,TRUE).';',$this->phpCode);
  }
}
