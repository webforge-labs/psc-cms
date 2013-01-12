<?php

namespace Psc\Doctrine;

class UnknownColumnException extends Exception {
  
  public function __construct(\PDOException $e) {
    parent::__construct($e->errorInfo[2], $e->errorInfo[1], $e);
  }
  
  public static function check(\PDOException $e) {
    return isset($e->errorInfo[0]) && $e->errorInfo[0] === '42S22';
  }
  
}

?>