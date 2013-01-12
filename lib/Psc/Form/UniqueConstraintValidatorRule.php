<?php

namespace Psc\Form;

use \Psc\ExceptionDelegator,
    \Psc\ExceptionListener,
    \Psc\CMS\ResponseController
;

class UniqueConstraintValidatorRule implements FieldValidatorRule, ExceptionListener {

  protected $uniqueConstraint;
  /**
   * @var ExceptionDelegator
   */
  protected $ed;
  
  /**
   * @var string
   */
  protected $field;
  protected $fieldData;
  
  protected $message;
  
  /**
   * @param string $message kein ein %s enhalten um den Wert des Keys für das der UniqueConstraint failed  als string ersetzt zu bekommen
   */
  public function __construct($uniqueConstraint, ExceptionDelegator $ed, $message) {
    $this->ed = $ed;
    $this->message = $message;
    $this->uniqueConstraint = $uniqueConstraint;

    $this->ed->subscribeException('Psc\Doctrine\UniqueConstraintException', $this);
  }
  
  public function listenException(\Exception $e) {
    if (!($e instanceof \Psc\Doctrine\UniqueConstraintException)) {
      throw new Exception('Falscher Exception-Type für Listener');
    }
    
    if ($e->uniqueConstraint == $this->uniqueConstraint) {
      $ex = new ValidatorException(sprintf($this->message, (string) $this->fieldData));
      $ex->field = $this->field;
    
      return $ex;
    }
  }
  
  public function validate($data) {
    if ($data === NULL) throw new EmptyDataException();
	
    $this->fieldData = $data;
    
    return $data;
  }
  
  public function setField($field) {
    $this->field = $field;
  }
}

?>