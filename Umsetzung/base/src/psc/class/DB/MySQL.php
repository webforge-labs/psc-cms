<?php

class DBMySQL extends DB {

  /**
   * stellt ein Query an die Datenbank und gibt das Resource-Handle zurück
   *
   * $this->lastQuery wird auf den letzten SQL Befehl gesetzt
   * @param string $sql
   * @return resource
   */
  public function query($sql) {
    $sql = Code::forceString($sql);
    
    if ($sql == '') {
      throw new Exception('Befehl war leer. '.self::varInfo($sql));
    }

    $this->registerQuery($sql);
   
    $q = mysql_query($sql, $this->connection->getResource());

    if ($q === FALSE) {
      throw $this->createException('Query',mysql_error(),mysql_errno());
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
}


?>