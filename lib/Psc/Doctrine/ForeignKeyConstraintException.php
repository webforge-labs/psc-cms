<?php

namespace Psc\Doctrine;

/* Foreign Key Constraint Exception */
class ForeignKeyConstraintException extends Exception {
  
  public $foreignKey;
  
  public function __construct(\PDOException $e) {
    parent::__construct($e->getMessage());
  }

  
  public static function check(\PDOException $e) {
    return isset($e->errorInfo[0]) && $e->errorInfo[0] == 'HY000';
  }
  
}

?>