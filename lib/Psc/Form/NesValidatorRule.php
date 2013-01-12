<?php

namespace Psc\Form;

class NesValidatorRule implements ValidatorRule {
  
  public function validate($data) {
    if ($data === NULL) throw new EmptyDataException();
    
    $data = trim($data);
    if (mb_strlen($data) > 0) {
      return $data;
    }
    
    throw new EmptyDataException('Länge von 0'); // dies soll auch optional fall sein: 14.08.2012
  }
}
?>