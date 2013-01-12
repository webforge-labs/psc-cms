<?php

namespace Psc;

class ConfigMissingVariableException extends \Psc\Exception {
  
  public $phpCode;
  
  public $keys;
  
  public function __construct ($message = "", $code = 0, \Exception $previous = NULL) {
    if (is_array($message)) {
      $this->keys = $message;
      
      $message = sprintf("Config-Variable: '%s' (default: ) nicht gefunden",
                         implode('.',$this->keys));
      $this->phpCode = '$GLOBALS[\'conf\']'.A::join($this->keys, "['%s']").' = NULL;';
    }
      
    parent::__construct($message,$code,$previous);
  }
  
  public function setDefault($default) {
    $this->message = str_replace('(default: )','(default: '.var_export($default,TRUE).';)',$this->message);
    $this->phpCode = str_replace(' = NULL;',' = '.var_export($default,TRUE).';',$this->phpCode);
  }
  
}

?>