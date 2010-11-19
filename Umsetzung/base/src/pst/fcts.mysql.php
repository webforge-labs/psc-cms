<?php

class MySQLException extends Exception {}
class MySQLConnectionException extends MySQLException {}

function mysql_db_connect($connection = NULL) {
  if (is_resource($connection))
    return $connection;

  if (empty($connection))
    $connection = 'default';

  if (is_string($connection) && array_key_exists($GLOBALS['conf']['db'][$connection])) {
    if (!isset($GLOBALS['env']['db'][$connection])) {
      $conf = $GLOBALS['conf']['db'][$connection];

      if (($GLOBALS['env']['db'][$connection] = mysql_connect($conf['host'],$conf['user'],$conf['password'])) == FALSE)
        throw new MySQLConnectionException('Cannot connect to database: '.$conf['user'].'@'.$conf['host'].', config: '.$connection);
      
      
      if (!mysql_select_db($conf['database'],$GLOBALS['env']['db'][$connection]))
        throw new MySQLConnectionException('Cannot select database: '.$conf['database'].' config: '.$connection);
    }

    return $GLOBALS['env']['db'][$connection];
  } else {
    throw new Exception('Configuration for Database: '.$connection.' not found!');
  }
}


function mysql_fetchresult($query, $con = NULL) {
  $con = mysql_db_connect($con);

  if (is_resource($query)) {
    $result_handle = $query;
  } else {
    $result_handle=mysql_q($query, $con);
  }

  $result=array();
  if (mysql_num_rows($result_handle) > 0) {
    while ($row = mysql_fetch_assoc($result_handle)) {
      array_push($result, $row);
    }
  }

  @mysql_free_result($result_handle);

  return $result;
}



function mysql_fetchfield($sql, $field = NULL, $con = NULL) {
  $con = mysql_db_connect($con);

  $res = mysql_fetchresult($sql,$con);
  $row = array_pop($res);

  if ($field === NULL) {
    return array_pop($row);
  } else {
    if (!array_key_exists($field,$row))
      throw new Exception('Feld: '.$field.' war nicht im Ergebnis');
  
    return $row[$field];
  }
}


function mysql_clean($arr, $con = NULL) {
  $con = mysql_db_connect($con);
  if (!is_array($arr)) return(mysql_real_escape_string($arr,$con));
  
  $ret=array();
  foreach($arr as $key=>$val) {
    if (is_array($val)) {
      $ret[$key]=mysql_clean($val);
    } else {
      $ret[$key]=mysql_real_escape_string($val,$con);
    }
  }

  return($ret);
}


function mysql_q($sql, $con = NULL) {
  $con = mysql_db_connect($con);

  $q = mysql_query($sql,$con);

  if ($q === FALSE) {
    $e = new MySQLException('Datenbank fehler: '.mysql_error());
    $e->sql = $sql;
    throw $e;
  }

  return $q;
}

?>