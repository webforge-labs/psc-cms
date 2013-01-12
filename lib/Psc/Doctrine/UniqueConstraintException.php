<?php

namespace Psc\Doctrine;

class UniqueConstraintException extends Exception {
  
  /**
   * Der Name des Constraints
   *
   * @var string
   */
  public $uniqueConstraint;
  
  /**
   * @var int|null
   */
  public $duplicateIdentifier;

  /**
   * @var array
   */
  public $duplicateKey;
  
  public function __construct(\PDOException $e = NULL) {
    if (isset($e)) {
      parent::__construct($e->errorInfo[2], $e->errorInfo[1]);
    
      $this->uniqueConstraint = \Psc\Preg::qmatch($e->errorInfo[2],"/key\s+'(.*?)'/");
    }
  }
  
  public static function factory($name, $causingEntry, Array $duplicateKey = NULL, $duplicateIdentifier = NULL) {
    $e = new static(NULL);
    $e->uniqueConstraint = $name;
    $e->duplicateIdentifier = $duplicateIdentifier;
    $e->duplicateKey = $duplicateKey;
    $e->setMessage("Duplicate entry '".$causingEntry."' for key '".$name."'");
    return $e;
  }
  
  public static function check(\PDOException $e) {
    return isset($e->errorInfo[0]) && $e->errorInfo[0] == '23000';
  }
  
}

?>