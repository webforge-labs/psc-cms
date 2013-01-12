<?php

namespace Psc\Form;

use \Psc\Code\Code;

class ArrayValidatorRule implements ValidatorRule {
  
  public function validate($data) {
	if ($data === NULL) throw EmptyDataException::factory(array());
    $data = Code::castArray($data);
    if (count($data) == 0) throw EmptyDataException::factory(array());
	
    if (count($data) > 0) {
      return $data;
    }
    
    throw new \Psc\Exception('Konnte nicht validiert werden');
  }
}

?>