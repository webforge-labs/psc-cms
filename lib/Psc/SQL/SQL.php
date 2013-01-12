<?php

namespace Psc\SQL;

use \Psc\Code\Code,
    \Psc\DB\DB,
    \Psc\Config
;

/**
 * Tabellen Namen egal
 * Primäry key ist 'id'
 * fremdschlüssel ist 'tabellenname_id'
 * Zwischentabelle - kein künstlicher Schlüssel. Name: tabelle12tabelle2 dabei ist die reihenfolge welche vorne steht alphabetisch
 */

class SQL extends \Psc\Object {
  
  const LEFT = 'LEFT ';
  const NORMAL = '';
  
  protected static $fctsAliases = array('uts'=>'UNIX_TIMESTAMP');
  
  /**
   * 
   * LEFT JOIN $tableJoin ON $tableOn
   * bei 1:n muss $tableOn die Fremdschlüssel-Spalte besitzen
   * bei n:1 genau umgekehrt
   * bei n:n muss es eine zwischentabelle geben
   * @param mixed $tableJoin kann ein array sein (tabelle,primary_key,foreign_key)
   * @param mixed $tableOn kann ein array sein (tabelle,primary_key,foreign_key)
   * @param string $joinAlias der Alias für TableJoin
   */
  public static function JOIN($tableJoin, $tableOn, $type = '1:n', $joinAlias = NULL, $joinType = self::NORMAL) {
    $type = Code::value($type, '1:n','n:1','n:n');
    
    if (is_array($tableJoin)) list ($tableJoin,$tableJoin_pk,$tableJoin_fk) = $tableJoin;
    if (is_array($tableOn)) list ($tableOn,$tableOn_pk,$tableOn_fk) = $tableOn;
    if (!isset($tableJoin_fk)) $tableJoin_fk = $tableOn.'_id';
    if (!isset($tableJoin_pk)) $tableJoin_pk = 'id';
    if (!isset($tableOn_fk)) $tableOn_fk = $tableJoin.'_id';
    if (!isset($tableOn_pk)) $tableOn_pk = 'id';
    
    $alias = isset($joinAlias) ? 'AS '.$joinAlias.' ' : NULL;
    $tableJoinAlias = isset($joinAlias) ? $joinAlias : $tableJoin;
    
    if ($type == '1:n') {
      $sql = $joinType.'JOIN '.SQL::quote($tableJoin).' '.$alias;
      $sql .= 'ON '.SQL::quote($tableOn,$tableOn_fk).' = '.SQL::quote($tableJoinAlias,$tableJoin_pk).' ';
    } 


    elseif ($type == 'n:1') {
      $sql = $joinType.'JOIN '.SQL::quote($tableJoin).' '.$alias;
      $sql .= 'ON '.SQL::quote($tableJoinAlias,$tableJoin_fk).' = '.SQL::quote($tableOn,$tableOn_pk).' ';
    }


    elseif ($type == 'n:n') {
      /* zwischentabelle */
      $relationTable = SQL::getRelationTable($tableJoin, $tableOn);

      $sql = $joinType.'JOIN '.SQL::quote($relationTable).' ';
      $sql .= 'ON '.SQL::quote($relationTable,$tableJoin_fk).' = '.SQL::quote($tableOn,$tableOn_pk).' ';

      $sql .= $joinType.'JOIN '.SQL::quote($tableJoin).' '.$alias;
      $sql .= 'ON '.SQL::quote($relationTable,$tableOn_fk).' = '.SQL::quote($tableJoinAlias,$tableJoin_pk).' ';
    }

    return $sql;
  }
  
  /**
   * 
   * LEFT JOIN $tableJoin ON $tableOn
   * bei 1:n muss $tableOn die Fremdschlüssel-Spalte besitzen
   * bei n:1 genau umgekehrt
   * bei n:n muss es eine zwischentabelle geben
   * @param mixed $tableJoin kann ein array sein (tabelle,primary_key,foreign_key)
   * @param mixed $tableOn kann ein array sein (tabelle,primary_key,foreign_key)
   * @param string $joinAlias der Alias für TableJoin
   */
  public static function LEFTJOIN($tableJoin, $tableOn, $type = '1:n', $joinAlias = NULL) {
    return self::JOIN($tableJoin,$tableOn, $type, $joinAlias,self::LEFT);
  }


  public static function getRelationTable($table1, $table2) {
    list($table1, $table2) = (strcmp($table1,$table2) < 0 ? array($table1,$table2) : array($table2,$table1));
    
    return $table1.'2'.$table2;
  }
  

  public static function quote($name, $name2 = NULL) {
    if (isset($name2))
      return '`'.$name.'`.`'.$name2.'`';
    else
      return '`'.$name.'`';
  }

  /**
   * 
   * andes als Quote macht diese Funktion noch einige checks über den String
   * performanter ist natürlich quote() zu benutzen, aber manchmal ist autoQuote gut dafür geeignet
   * Usereingaben flexibel zu machen.
   */
  public static function autoQuote($name) {
    if (is_array($name))
      return SQL::quote($name[0], isset($name[1]) ? $name[1] : NULL);

      /* ist der name schon gequoted? */
    if (is_integer($name) || mb_substr($name,0,1) == '`')
      return $name;

    if (($p = mb_strrpos($name,'.')) !== FALSE) { //tabelle.column
      return SQL::quote(mb_substr($name,0,$p), mb_substr($name,$p+1));
    }
    
    return SQL::quote($name);
  }
  
  /**
   *
   * Formatiert einen SELECT-Part eines SQLs
   * Alle Spalten werden mit tabelle%spalte ge-aliased. Der Separator kann durch Config:DB.as_separator bestimmt werden
   * 
   * Parameter:
   * SELECT('tabelle.*');
   * SELECT('tabelle.spalte');
   *
   * Usage:
   * $sql = 'SELECT '.SQL::SELECT('tabelle.spalte','tabelle2.*').' FROM tabelle, tabelle2 ';
   * 
   * @param mixed $what
   * @return string
   */
  public static function SELECT() {
    $sql = NULL;
    
    foreach (func_get_args() as $what) {
      /* alle parts hinterlassen immer einen String der mit einem ", " endet */
      
  // ('$table.*') => ($table, '*')
      if (is_string($what) && mb_strpos($what, '*') !== FALSE && !mb_strpos($what,',') !== FALSE) {
        $what = array(mb_substr($what,0,mb_strpos($what,'.')),'*');  // Aufruf SELECT($table,'*')
      }
      
      if (is_array($what)) {
        
  // ('$table.$column', ... , ... ,...) => ($table,$column, ... , ..., ...)
        if (count($what) > 1 && mb_strpos($what[0],'.') !== FALSE) {
          array_unshift($what,mb_substr($what[0],0,mb_strpos($what[0],'.'))); // table ganz vorne hinzufügen
          $what[1] = mb_substr($what[1],mb_strpos($what[1],'.')+1); // von table.column nur column nehmen
        }
        
        
  // ($table,'*')
        if (count($what) == 2 && $what[1] == '*') { 
          $table = $what[0];
          $columns = DB::instance()->getColumns($table);
          $sep = Config::req('DB','as_separator');
          
          foreach ($columns as $column) {
            $sql .= self::quote($table,$column['name'])." AS '".$table.$sep.$column['name']."', ";
          }
        }
    
  // ($table,$columnName)
        if (count($what) == 2 && $what[1] != '*') {
          $sql .= self::quote($table,$what[1])." AS '".$table.$sep.$what[1]."', ";
        }
        
  // ($table,$columnName,$functionAlias)
        if (count($what) == 3 && $what[1] != '*' && array_key_exists($what[2], self::$fctsAliases)) {
          $what[3] = $what[2];
          $what[2] = self::$fctsAliases[$what[2]];

        }
        
  // ($table, $columnName, $function, $functionAlias)
        if (count($what) == 4 && $what[1] != '*') {
          $sql .= $what[2].'('.self::quote($table,$what[1]).") AS '".$table.$sep.$what[1].$sep.$what[3]."', ";
        }
      }
    }
    
    if (mb_strlen($sql) > 2)  $sql = mb_substr($sql,0,-2); // letztes Komma abschneiden
    
    return $sql;
  }
  
  
  public static function get(Array $row, $table, $column, $function = NULL) {
    $sep = Config::req('DB','as_separator');
    
    if (!isset($function)) {
      return $row[$table.$sep.$column];
    } else {
      return $row[$table.$sep.$column.$sep.$function];
    }
  }


  /**
   * Gibt den SET Teil eines Updates/Inserts zurück
   *
   * der Trick ist, das immer column und value alternierend (bzw paarweise) genommen werden:<br />
   * Parameter 1 ist also ein Spaltenname, dann ist Parameter 2 der Wert für den Spaltenname
   * Dann ist Parameter 3 ein Spaltenname und Parameter 4 der Wert zum Spaltenname aus Parameter 3 usw.<br />
   * Also immer abwechselnd.<br />
   * <code>
   *  $sql .= "UPDATE bananenbrote ";
   *  $sql .= q::set('id',$this->getId(),
   *                 'name',$this->name,
   *                 'timestamp',$timestamp);
   *  $sql .= "WHERE id = 34";
   * </code>
   * <code>
   *   $sql .= "UPDATE bananenbrote ";
   *   $sql .= q::set(
   *       array(
   *           'id'=>$this->getId(),
   *           'name'=>$this->name,
   *           'timestamp'=>$timestamp
   *           ),
   *           q::ASSOC
   *       );
   *   $sql .= "WHERE id = 34";
   * </code>
   * Es wird ein Weißzeichen am Ende angehängt.
   * @param DB $db
   * @param string $column der Name der Spalte (ohne Quotes)
   * @param mixed $value der PHPWert (wird umgewandelt)
   * @param string $column der Name der Spalte (ohne Quotes)
   * @param mixed $value der PHPWert (wird umgewandelt)
   * @param string $column der Name der Spalte (ohne Quotes)
   * @param mixed $value der PHPWert (wird umgewandelt)
   * @param string $column,...
   * @param mixed $value,...
   * @return string (ein zusätzliches Weißzeichen am Ende)
   */
  public static function set() {
    $args = func_get_args();
    $db = array_shift($args);
    $argsNum = count($args);

    /* Aufruft für setWithAssocArray */
    if ($argsNum == 2 && is_array($args[0]) && $args[1] === self::ASSOC) {
      return self::setWithAssocArray($db,$args[0]);
    }

    if ($argsNum % 2 != 0) {
      throw new Exception('falsche Argument Anzahl (muss gerade sein).');
    }

    $sql = 'SET ';
    for ($i=0;$i<$argsNum;$i++) {
      
      if ($i % 2 == 0) {
        $sql .= self::quote($args[$i]).' = ';
      } else {
        $sql .= $db->convertValue($args[$i]);

        if ($i != $argsNum-1)
          $sql .= ', ';
      }
    }
    
    return $sql.' ';
  }

  /**
   * Wie set() nur mit einem Array
   * 
   * Die Funktion wird von set() aufgerufen, wenn diese nur mit einem Array aufgerufen wird
   * Die Schlüssel des Arrays werden als Spalten interpretiert.
   * @param array $map schlüssel sind Spaltennamen, Werte sind werte (werden umgewandelt
   * @todo außer die logik zu kopieren, fällt mir hier einfach nichts besseres ein.
   */
  public static function setWithAssocArray(DB $db, Array $map) {
    $sql = 'SET ';

    $it = new \ArrayIterator($map); // spl
    while($it->valid()) {
      $sql .= self::autoQuote($it->key()).' = ';
      $sql .= $db->convertValue($it->current());
      
      $it->next();
      
      if ($it->valid())
        $sql .= ', ';
    }
    
    return $sql.' ';
  }
}

?>