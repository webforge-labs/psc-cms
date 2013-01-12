<?php

namespace Psc\DB;

use \Psc\Code\Code;

class PDOStatement {  
  
  protected $statement;
  
  protected $pdo;
  
  public function __construct(\PDOStatement $statement, PDO $pdo) {  
    $this->statement = $statement;
    $this->pdo = $pdo;
  }  
  
  public function execute(array $input_parameters = array()) {  
    $result = $this->statement->execute($input_parameters);
    $this->pdo->log($this->statement->queryString);
    
    return $result;  
  } 

  public function __call($method, $params = array()) {
    return call_user_func_array(array($this->statement,$method), $params);
  }
}

?>