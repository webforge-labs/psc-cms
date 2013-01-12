<?php

namespace Psc;

class EmptyDataInputException extends \Psc\DataInputException {
  
  public function __construct ($message = "", $code = 0, Exception $previous = NULL) {
    parent::__construct($message,$code,$previous);
    $this->empty = TRUE;
  }

}

?>
