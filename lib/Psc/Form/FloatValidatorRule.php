<?php

namespace Psc\Form;

use Psc\Exception;

class FloatValidatorRule implements ValidatorRule {
  
  protected $zero;
  
  /**
   * validiert einen Float mit , als Dezimaltrenner und . als TausendSeperator
   */
  public function __construct($zero = FALSE) {
    $this->zero = $zero;
  }
  
  public function validate($data) {
    if ($data === NULL) throw EmptyDataException::factory(0.0);
    if ($data === '') throw EmptyDataException::factory(0.0);

    if (is_string($data)) {
      $float = \Psc\Code\Numbers::parseFloat($data, '.', ',');
    } elseif(is_numeric($data)) {
      $float = (float) $data;
    } else {
      throw new Exception('Parsing von '.$data.' war nicht möglich. String|Integer erwartet');
    }
    
    if (!is_float($float)) {
      throw new Exception('Parsing von '.$data.' war nicht möglich');
    }
    
    if (!$this->zero && $float == 0)
      throw new Exception('0 ist nicht erlaubt');
    
    return $float;
  }
}
?>