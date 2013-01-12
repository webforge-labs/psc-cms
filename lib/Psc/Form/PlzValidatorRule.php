<?php

namespace Psc\Form;

use \Psc\Preg;

class PlzValidatorRule implements ValidatorRule {
  
  public function validate($data) {
    if ($data === NULL) throw new EmptyDataException();
	
    $data = trim($data);
    // 60345+ und [A-Z]-340545345345, etc
    if (Preg::match($data,'/[0-9+]/') > 0 || Preg::match($data,'/[A-Z]+-[0-9+]/')) {
      return $data;
    }
    
    throw new \Psc\Exception('ist keine valide Postleitzahl');
  }
}

?>