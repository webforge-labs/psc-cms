<?php

namespace Psc\DB;

use \Psc\Code\Code,
    \Psc\Config,
    \Psc\A,
    \Psc\Doctrine\Helper as DoctrineHelper
  ;

class PDO extends \PDO implements \Doctrine\DBAL\Logging\SQLLogger {
  
  public $queries = array();

  public function __construct($con = NULL) {
    $con = Code::forceDefString($con, 'default');
    $conf = Config::req('db',$con);
    
    $dsn = 'mysql:';
    if (isset($conf['host']))
      $dsn .= 'host='.$conf['host'].';';
    if (isset($conf['database']))
      $dsn .= 'dbname='.$conf['database'].';';
    if (isset($conf['charset']))
      $dsn .= 'charset='.$conf['charset'].';';
    
    parent::__construct($dsn, $conf['user'],$conf['password'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    
    if (isset($conf['charset'])) {
      $this->query("SET CHARACTER SET '".$conf['charset']."'");
    }
  }
  
  public function prepare($statement, $driver_options = array()) {
    // inherit
    $stmt = parent::prepare($statement, $driver_options);
    
    // wrap
    $statement = new PDOStatement($stmt, $this);
    
    return $statement;
  }
  
  public function exec($statement) {
    $this->log($statement);
    return parent::exec($statement);
  }
  
  public function query($sql) {
    $this->log($sql);
    //print trim($sql)."\n";

    $args = func_get_args();
    if (count($args) == 1) {
      return parent::query($args[0]);
    } elseif (count($args) == 2) {
      return parent::query($args[0],$args[1]);
    } elseif (count($args) == 3) {
      return parent::query($args[0],$args[1],$args[2]);
    }
  }
  
  public function log($sql) {
    $this->queries[] = trim($sql);
  }
  
  /**
   * @return array
   */
  public function fetchResult($sql, $mode = self::FETCH_OBJ) {
    $res = array();

    $q = $this->query($sql);

    foreach ($q->fetchAll($mode) as $row) {
      $res[] = $row;
    }
    return $res;
  }
  
  public function fetchNum($sql) {
    $q = $this->query($sql);
    
    //agagag
    return count($this->fetchResult($sql));
  }
  
  public function lastQuery() {
    return A::peek($this->queries);
  }
  
  public function registerWithDoctrine() {
    DoctrineHelper::em()->getConnection()->getConfiguration()->setSQLLogger($this);
  }
  
  public function __toString() {
    $str = NULL;
    foreach($this->queries as $sql) {
      $str .= '[DB] '.str_replace("\n"," ",$sql)."\n";
    }
    return $str;
  }

  /* INTERFACE SQLLogger */
  public function startQuery($sql, array $params = null, array $types = null) {
    $this->log($sql);
  }

  public function stopQuery() {
    
  }
  /* END INTERFACE SQLLogger */
}