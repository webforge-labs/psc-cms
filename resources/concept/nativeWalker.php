<?php

class Walker {
  
  public function walkArray(Array $array) {
    foreach ($array as $key => $value) {
      $this->walkValue($value);
    }
  }
  
  public function walkValue($value) {
    switch(gettype($value)) {
      
      case 'string':
        $this->walkString($value);
        break;
      
      case 'integer':
        $this->walkInteger($value);
        
      // usw
    }
  }
  
  public function walkString($string) {
    // ...
  }

  public function walkInteger($int) {
    // ...
  }
}
?>