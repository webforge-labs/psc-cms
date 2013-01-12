<?php

namespace Psc\Form;

use \Psc\Code\Code;

class ValuesValidatorRule implements ValidatorRule {
  
  protected $values;
  
  public function __construct(Array $values) {
    $this->values = $values;
  }
  
  public function validate($data) {
    if ($data === NULL) throw new EmptyDataException();
    
    $data = trim($data);
	
    if (in_array($data,$this->values)) {
      return $data;
    }
    
    throw new \Psc\Exception('Erlaubte Werte sind: '.implode('|',$this->values). '"'.$data.'" erhalten');
  }
}
?>