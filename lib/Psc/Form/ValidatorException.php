<?php

namespace Psc\Form;

class ValidatorException extends \Psc\Exception {
  
  public $field;
  public $data;
  
  public $label; // kann gesetzt werden vom controller, muss aber nich
}

?>