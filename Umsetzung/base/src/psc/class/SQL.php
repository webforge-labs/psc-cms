<?php

/**
 * Tabellen Namen egal
 * Primäry key ist 'id'
 * fremdschlüssel ist 'tabellenname_id'
 * Zwischentabelle - kein künstlicher Schlüssel. Name: tabelle12tabelle2 dabei ist die reihenfolge welche vorne steht alphabetisch
 */

class SQL extends Object {
  
  /**
   * 
   * LEFT JOIN $tableJoin ON $tableOn
   * bei 1:n muss $tableOn die Fremdschlüssel-Spalte besitzen
   * bei n:1 genau umgekehrt
   * bei n:n muss es eine zwischentabelle geben
   * @param mixed $tableJoin kann ein array sein (tabelle,primary_key,foreign_key)
   * @param mixed $tableOn kann ein array sein (tabelle,primary_key,foreign_key)
   */
  public static function LEFTJOIN($tableJoin, $tableOn, $type = '1:n') {
    $type = Code::value($type, '1:n','n:1','n:n');
    
    if (is_array($tableJoin)) list ($tableJoin,$tableJoin_pk,$tableJoin_fk) = $tableJoin;
    if (is_array($tableOn)) list ($tableOn,$tableOn_pk,$tableOn_fk) = $tableOn;
    if (!isset($tableJoin_fk)) $tableJoin_fk = $tableOn.'_id';
    if (!isset($tableJoin_pk)) $tableJoin_pk = 'id';
    if (!isset($tableOn_fk)) $tableOn_fk = $tableJoin.'_id';
    if (!isset($tableOn_pk)) $tableOn_pk = 'id';
    
    if ($type == '1:n') {
      $sql = 'LEFT JOIN '.SQL::quote($tableJoin).' ';
      $sql .= 'ON '.SQL::quote($tableOn,$tableOn_fk).' = '.SQL::quote($tableJoin,$tableJoin_pk).' ';
    } 


    elseif ($type == 'n:1') {
      $sql = 'LEFT JOIN '.SQL::quote($tableJoin).' ';
      $sql .= 'ON '.SQL::quote($tableJoin,$tableJoin_fk).' = '.SQL::quote($tableOn,$tableOn_pk).' ';
    }


    elseif ($type == 'n:n') {
      /* zwischentabelle */
      $relationTable = SQL::getRelationTable($tableJoin, $tableOn);

      $sql = 'LEFT JOIN '.SQL::quote($relationTable).' ';
      $sql .= 'ON '.SQL::quote($relationTable,$tableJoin_fk).' = '.SQL::quote($tableOn,$tableOn_pk).' ';

      $sql .= 'LEFT JOIN '.SQL::quote($tableJoin).' ';
      $sql .= 'ON '.SQL::quote($relationTable,$tableOn_fk).' = '.SQL::quote($tableJoin,$tableJoin_pk).' ';
    }

    return $sql;
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
   * Usereingabe flexibel zu machen.
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
}

?>