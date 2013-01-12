<?php

namespace Psc\Form;

class IntegerValidatorRule implements ValidatorRule {
  
  protected $zeroAllowed = FALSE;
  
  public function __construct($zeroAllowed = FALSE) {
    $this->setZeroAllowed($zeroAllowed);
  }
  
  public function validate($data) {
	if ($data === NULL) throw new EmptyDataException();
    if ($data == 0 && !$this->zeroAllowed) throw new EmptyDataException();
	
    $data = (int) $data;
    if ($data === 0 && $this->zeroAllowed || $data != 0) {
      return $data;
    }
    
    throw new \Psc\Exception('Positive Zahl erwartet. '.$this->zeroAllowed ? '0 erlaubt' : '0 nicht erlaubt');
  }
  
  /**
   * @param bool $zeroallowed
   * @chainable
   */
  public function setZeroAllowed($zeroallowed) {
    $this->zeroAllowed = $zeroallowed;
    return $this;
  }

  /**
   * @return bool
   */
  public function getZeroAllowed() {
    return $this->zeroAllowed;
  }
}

?>