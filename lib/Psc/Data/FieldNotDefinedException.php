<?php

namespace Psc\Data;

class FieldNotDefinedException extends \Psc\Data\Exception {
  
  public $field; // array von ebenen
  
  public $avaibleFields; // array von Strings
  
}
?>