<?php

namespace Psc\Form;

use \Psc\Code\Code;

class IdValidatorRule implements ValidatorRule {
  
  public function validate($data) {
    if ($data === NULL) throw new EmptyDataException();
    
    if (is_string($data)) $data = trim($data);
    
    $id = Code::castId($data);
    if ($id > 0) {
      return $id;
    }
    
    throw new \Psc\Exception('Zahl größer 0 erwartet');
  }
}

?>