<?php

class DBMySQLConnection extends DBConnection {

  /**
   * 
   * @param string Name der Verbindung
   */
  public function connect($con = NULL) {
    $con = Code::forceDefString($con, 'default');

    $conf = Config::get('db',$con);

    if (($connection = mysql_connect($conf['host'],$conf['user'],$conf['password'])) == FALSE)
      throw new MySQLConnectionException('Cannot connect to database: '.$conf['user'].'@'.$conf['host'].'. config: '.$con);
      
    if (!mysql_select_db($conf['database'],$connection))
      throw new MySQLConnectionException('Cannot select database: '.$conf['database'].'. config: '.$con);

    if (isset($conf['charset'])) {
      $q = mysql_query("SET CHARACTER SET '".$conf['charset']."'", $conection);
      if (!$q) throw new MySQLConnectionException('Cannot set Character set to: '.$conf['charset'].'. config: '.$con);
    }

    $this->connection = $connection;
  }
}

?>