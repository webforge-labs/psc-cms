<?php

namespace Psc\DB;

use \Psc\Code\Code;

class MySQL extends DB {

  /**
   * stellt ein Query an die Datenbank und gibt das Resource-Handle zurück
   *
   * $this->lastQuery wird auf den letzten SQL Befehl gesetzt
   * @param string $sql
   * @return resource
   */
  public function query($sql) {
    $sql = (string) $sql;
    
    if ($sql == '') {
      throw new Exception('Befehl war leer. '.self::varInfo($sql));
    }

    $this->registerQuery($sql);
    $link = $this->connection->getResource();
   
    $q = mysql_query($sql, $link);

    if ($q === FALSE) {
      throw $this->createException('Query',mysql_error($link),mysql_errno($link));
    }
    
    return $q;
  }

  /**
   * stellt eine Anfrage an die Datenbank und gibt das Ergebnis als Array zurück
   * 
   * die Funktion ist ähnlich wie die alte mysql_fetchresult. Sie kann entweder einen String oder eine Query Resource erhalten
   * @param string|resource $query
   * @return array SQL Result
   * @uses query()
   * @see getResourceIterator()
   */
  public function fetch($query) {
    $ret = array();

    if (!is_resource($query))
      $q = $this->query($query);
    
    while ($res = mysql_fetch_assoc($q)) {
      $ret[] = $res;
    }
    mysql_free_result($q);
    
    return $ret;
  }

  /**
   * Gibt aus einem Select das erste oder benannte Feld der ersten Zeile zurück
   * 
   * @param string|resource $query
   * @uses fetch
   * @return mixed
   */
  public function fetchfield($query, $field = NULL) {
    $res = $this->fetch($query);
    
    $row = array_pop($res);

    if ($field === NULL) {
      return array_pop($row);
    } else {
      if (!array_key_exists($field,$row))
        throw new Exception('Feld: '.$field.' war nicht im Ergebnis.');
  
      return $row[$field];
    }
  }
  
  
  
   /**
   * Escaped einen String zum Verwenden in einem Query
   * @param string $str 
   * @return string
   */
  public function escapeString ($string) {
    return mysql_real_escape_string($string, $this->getConnection()->getResource());
  }

  /**
   * Transponiert einen PHP Wert in einen Datenbank wert
   * 
   * @param mixed|NULL $value
   * @return transponierte und gecleante value (für strings) 
   * @exception wenn der PHPType nicht umgewandelt werden kann
   */
  public function convertValue($value=NULL) {
    
    if (is_null($value)) {
      $mysqlValue = 'NULL';
    } elseif (is_integer($value)) {
      $mysqlValue = (int) $value;
    } elseif (is_float($value)) {
      trigger_error('missing implementation für float 2 mysql',E_USER_ERROR);
    } elseif (is_string($value)) {
      $mysqlValue = "'".$this->escapeString($value)."'";
    } else {
      throw new \Psc\Exception('PHP Typ kann nicht zu Mysql Typ umgewandelt werden. PHPTyp:'.Code::varInfo($value));
    }

    return $mysqlValue;
  }
  
  
  /**
   * Kopiert eine Tabelle innerhalb der Datenbank
   *
   * @param string $srcTableName
   * @param string $targetTableName
   * @param bitmap $type Daten, Struktur oder beides
   */
  public function copyTable($srcTableName, $targetTableName, $type = self::COPY_STRUCTURE_AND_DATA) {
    $tables = $this->getTables();
    
    if (!in_array($srcTableName, $tables)) {
      $this->createException('NoSuchTable','Tabelle :'.$srcTableName.' nicht gefunden');
    }
    
    if ($type & self::COPY_DATA && $type ^ self::COPY_STRUCTURE && !in_array($targetTableName, $tables)) {
      $this->createException('NoSuchTable','Tabelle :'.$targetTableName.' nicht gefunden');
    }
    
    if ($type & self::COPY_STRUCTURE) {
      $sql = "SHOW CREATE TABLE `".$srcTableName."` ";
      
      $res = $this->fetch($sql);
      $sqlCreate = $res[0]['Create Table'];
      
      $sqlStructure = preg::replace($sqlCreate,'/CREATE TABLE `(.*?)`/', 'CREATE TABLE `'.$targetTableName.'`');
      
      $this->query($sqlStructure);
    }
    
    if ($type & self::COPY_DATA) {
      $sqlData = "INSERT INTO `".$targetTableName."` SELECT * FROM `".$srcTableName."` ";
      $this->query($sqlData);
    }
    
    return $this;
  }
}


?>