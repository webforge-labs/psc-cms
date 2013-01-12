<?php

namespace Psc\Form;

/**
 * 
 */
class ValidatorExceptionList extends \Psc\Exception {
  
  /**
   * @var Psc\Form\ValidatorException[]
   */
  protected $exceptions;
  
  public function __construct(Array $exceptions) {
    $this->setExceptions($exceptions);
    
    $msg = "Exception List with Exceptions:\n";
    foreach ($exceptions as $key=>$exception) {
      $msg .= sprintf(" #%d %s\n", $key+1, $exception->getMessage());
    }
    
    parent::__construct($msg);
  }
  
  public function addException(ValidatorException $e) {
    $this->exceptions[] = $e;
    return $this;
  }
  
  /**
   * @param Psc\Form\ValidatorException[] $exceptions
   */
  public function setExceptions(Array $exceptions) {
    $this->exceptions = $exceptions;
    return $this;
  }
  
  /**
   * @return Psc\Form\ValidatorException[]
   */
  public function getExceptions() {
    return $this->exceptions;
  }
}
?>