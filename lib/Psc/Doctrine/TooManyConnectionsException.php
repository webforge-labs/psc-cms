<?php

namespace Psc\Doctrine;

class TooManyConnectionsException extends Exception {
  
  public function __construct(\PDOException $e) {
    parent::__construct($e->errorInfo[2], $e->errorInfo[1]);
  }
  
  public static function check(\PDOException $e) {
    return isset($e->errorInfo[0]) && $e->errorInfo[0] == '08004';
  }
  
}

?>