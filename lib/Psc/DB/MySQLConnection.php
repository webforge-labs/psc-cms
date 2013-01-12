<?php

namespace Psc\DB;

use \Psc\Code\Code,
    \Psc\Config,
    \Psc\ConfigMissingVariableException
;

class MySQLConnection extends Connection {

  /**
   * 
   * @param string Name der Verbindung
   */
  public function connect($con = NULL) {
    $this->con = Code::forceDefString($con, 'default');

    try {
      $conf = Config::req('db',$this->con);
      $prefix = 'db.'.$this->con.'.';
    
      $this->port = (Config::get($prefix.'port') > 0) ? (int) Config::get($prefix.'port') : NULL;
      $this->host = Config::req($prefix.'host');
      $this->user = Config::req($prefix.'user');
      $this->database = Config::get($prefix.'database');
      $this->charset = Config::get($prefix.'charset');
      
      $port = (isset($this->port)) ? ':'.$this->port : NULL;
      if (($connection = mysql_connect($this->host.$port,$this->user,Config::req($prefix.'password'),TRUE)) == FALSE)
        throw new MySQLConnectionException('Cannot connect to database: '.$this->user.'@'.$this->host.$port.'. config: '.$this->con);
   
      $this->connection = $connection;
        
      if (!empty($this->database)) {
        $this->selectDatabase(Config::req($prefix.'database'));
      }
  
      if (!empty($this->charset)) {
        $q = mysql_query("SET CHARACTER SET '".$this->charset."'", $this->connection);
        if (!$q) throw new MySQLConnectionException('Cannot set Character set to: '.$this->charset.'. config: '.$this->con);
      }
    } catch (ConfigMissingVariableException $e) {
      throw new MisconfigurationException($this->con);
    }
  }
  
  
  public function selectDatabase($dbName) {
    if (!mysql_select_db($dbName,$this->connection))
      throw new MySQLConnectionException('Cannot select database: '.$dbName.'. config: '.$this->con);
    
    $this->database = $dbName;
    
    return $this;
  }
}

class MySQLConnectionException extends ConnectionException {  }
?>