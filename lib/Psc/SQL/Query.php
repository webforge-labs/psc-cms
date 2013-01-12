<?php

namespace Psc\SQL;

/**
 * 
 */
class Query extends \Psc\Object {

  protected $db;

  /**
   * 
   * @var array
   */
  protected $selects = array();

  /**
   * 
   * @var array
   */
  protected $from = array();

  /**
   * 
   * @var array
   */
  protected $orderBy = array();

  /**
   * 
   * @var array
   */
  protected $groupBy = array();

  /**
   * 
   * Die Bestandteile des Arrays werden mit AND verknüpft
   * @var array
   */
  protected $where = array();


  protected $sql = NULL;

  public function __construct($con = NULL) {
    $this->db = DB::instance($con);
  }

  /**
   * 
   * table.*
   * UNIX_TIMESTAMP('column') AS column_uts
   * table.id AS tableid
   * banane, wurst, schatulle
   * 
   * Achtung: Keine Funktionen mit mehreren Select Expressions (durch Komma getrennt) übergeben, wenn Funktionen mit Parametern übergeben werden
   * $sql->SELECT("IF(column = 'wert',1,'nein'), spalte AS alias, spalte2 AS alias") funktioniert nicht!
   * 
   * stattdessen:
   * $sql->SELECT("IF(column = 'wert',1,'nein')")
   * $sql->SELECT('spalte AS alias, spalte2 AS alias');
   * 
   * Die Funktion parsed Bestandteile des SQLs welches übergeben wird.
   * Wir machen dies mit sehr aufwändigen Funktionen, ermöglichen dadurch aber einfachere
   * Benutzung durch den Programmierer. Später einmal sollen Performance-Notices erstellt werden
   * die dann zur Optimierung Eingabe-Formate vorschlagen, die nicht geparsed werden müssen
   * @param string $selectExpression Kann auch $selectExpression, $selectExpression, ... sein
   * @param string $selectexpression, ...
   * @chainable
   */
  public function SELECT($selectExpression) {
    $selects = array();
    foreach (func_get_args() as $selectExpression) {
      /* überprüfen ob es sich um mehre Expressions handelt */
      if (is_string($selectExpression) && mb_strpos($selectExpression, ',') !== FALSE) {
        $selects = array_merge($selects, mb_split('\s*,\s*',$selectExpression));
      } else {
        $selects[] = $selectExpression; // überträgt arrays z.b. direkt
      }
    }

    $rxBacktick = '(?:`)';
    $rxQuote = "(?:')";
    $rxOBt = $rxBacktick.'?';                               // optional Backtick
    $rxOQuote = $rxQuote.'?';
    $rxTable = '[a-zA-Z_0-9]+';
    $rxColumn = '[a-zA-Z_0-9]+';
    $rxIdentifier = '[a-zA-Z_0-9]+';
    $rxAlias = '(?:\s+(?i)AS\s+|\s+)'.$rxOQuote.'('.$rxIdentifier.')'.$rxOQuote;

    foreach ($selects as $select) {
      $match = array();
    
      if (is_string($select)) {
        
        // table.* oder table.column
        if (preg::match($select, '/'.$rxOBt.'('.$rxTable.')'.$rxOBt.'\.(?:'.$rxOBt.'('.$rxColumn.')'.$rxOBt.'|(\*))(?:'.$rxAlias.')?/', $match)) {
          $this->selects[] = array('table'=>$match[1], 'column'=>$match[2], 'alias'=>(isset($match[3]) && mb_strlen($match[3]) > 0 ? $match[3] : NULL));
          continue;
        }

        // nur column
        if (preg::match($select, '/'.$rxOBt.'('.$rxColumn.')'.$rxOBt.'(?:'.$rxAlias.')?/', $match)) {
          $this->selects[] = array('table'=>NULL, 'column'=>$match[1], 'alias'=>(isset($match[2]) && mb_strlen($match[2]) > 0 ? $match[2] : NULL));
          continue;
        }

        if (preg::match($select, '/^\s*DISTINCT\s++(.+)\s*$/i', $match)) {
        }

      } elseif($select instanceof ORMObject) {
        
        ORM::select($this,$select);

      } else {
        $this->selects[] = $select;
      }
    }
      
    return $this;    
  }

  public function FROM($table) {
    if (!array_key_exists($table,$this->from)) 
      $this->from[$table] = SQL::quote($table);

    return $this;
  }

  /**
   * 
   * @var mixed $expression,...
   */
  public function WHERE($expression) {
    $args = func_get_args();
    $argsNum = count($args);

    if ($argsNum == 1 && is_array($args[0])) {
      $argsNum = count($args);
      $args = $args[0];
    }

    if ($argsNum == 1 && is_string($args[0])) {
      $this->where[] = $args[0];
    }

    if ($argsNum == 2) {
      $this->where[] = $args[0].' = '.$args[1];
    }

    if ($argsNum == 3) {
      $this->where[] = $args[0].' '.$args[1].' '.$args[2];
    }

    return $this;
  }

  /**
   * Setzt das LIMIT für das SQL Query
   * 
   * ist nur ein Parameter angegeben wird er als $maximum interpretiert
   * beide Parameter müssen positiv sein
   * @param int $offset 0-basierendes offset ab wann die $maximum Anzahl von Datensätze ins Result genommen werden sollen
   * @param int $maximum anzahl der Datensätze im Result
   */
  public function LIMIT() {
    $args = func_get_args();
    if (func_num_args() != 1 && func_num_args() != 2)
      throw new Exception('Limit kann nur 1 oder 2 Parameter erhalten');
    
    $this->limit = $args; // überträgt entweder array($maximum) oder array($offset,$maximum)

    return $this;
  }

  /**
   * 
   * @param string|array $columnName der Name einer Spalte
   * @param string ASC|DESC die Sortierreihenfolge
   */
  public function ORDERBY($columnName, $order = NULL) {
    if (is_array($columnName)) {
      $index = implode('.',$columnName);
    } else {
      if (($p = mb_strrpos(mb_strtoupper($columnName),'DESC')) !== FALSE || ($p = mb_strrpos(mb_strtoupper($columnName),'ASC')) !== FALSE) {
        $order = mb_substr(mb_strtoupper($columnName),$p);
        $columnName = mb_substr($columnName,0,$p);
      }
      $index = $columnName;
    }

    if (!array_key_exists($index,$this->orderBy))
      $this->orderBy[$index] = array(SQL::autoQuote($columnName),$order);
  }


  /**
   * 
   * @param string|array $columnName der Name einer Spalte
   * @param string ASC|DESC die Sortierreihenfolge
   */
  public function GROUPBY($columnName, $order = NULL) {
    if (is_array($columnName)) {
      $index = implode('.',$columnName);
    } else {
      if (($p = mb_strrpos(mb_strtoupper($columnName),'DESC')) !== FALSE || ($p = mb_strrpos(mb_strtoupper($columnName),'ASC')) !== FALSE) {
        $order = mb_substr(mb_strtoupper($columnName),$p);
        $columnName = mb_substr($columnName,0,$p);
      }
      $index = $columnName;
    }

    if (!array_key_exists($index,$this->orderBy))
      $this->groupBy[$index] = array(SQL::autoQuote($columnName),$order);
  }



  public function result() {
    if (isset($this->sql))
      return $sql = $this->sql;
    else {
      /* zuerst überprüfen wir ein paar geschichten: */

      if (count($this->from) == 0)
        throw new Exception('FROM ist nicht gesetzt');

      if (count($this->selects) == 0)
        throw new Exception('es ist nichts für SELECT ausgewählt');


      $br = "\n";
    
      $sqlSelect = $sqlWhere = $sqlLimit = $sqlOrderBy = $sqlGroupBy = NULL;

      /* SELECT */
      foreach ($this->selects as $sel) {
      
        if (is_array($sel)) {
        
          /* tabelle */
          if (isset($sel['table'])) {
            $sqlSelect .= SQL::quote($sel['table']).'.';
          }

          /* column */
          if ($sel['column'] == '*') 
            $sqlSelect .= '*';
          else
            $sqlSelect .= SQL::quote($sel['column']);
          
          /* alias */
          if (isset($sel['alias'])) {
            $sqlSelect .= " AS '".str_replace("'","\'",$sel['alias'])."'";
          }
        
        } elseif (is_string($sel)) {
          $sqlSelect .= $sel;
        }
      
        $sqlSelect .= ', ';
      }
      $sqlSelect = mb_substr($sqlSelect,0,-2);

      /* WHERE */
      if (count($this->where) > 0) {
        $sqlWhere = 'WHERE 1 '.A::join($this->where, ' AND (%s) '.$br).' '.$br;
      }

      /* ORDER BY */
      if (count($this->orderBy) > 0) {
        $sqlOrderBy = 'ORDER BY ';
        foreach ($this->orderBy as $ob) {
          $sqlOrderBy = $ob[0].(isset($ob[1]) ? ' '.$ob[1] : NULL).', ';
        }
        $sqlOrderBy = mb_substr($sqlOrderBy,0,-2);
      }

      /* GROUP BY */
      if (count($this->groupBy) > 0) {
        $sqlGroupBy = 'GROUP BY ';
        foreach ($this->groupBy as $ob) {
          $sqlGroupBy = $ob[0].(isset($ob[1]) ? ' '.$ob[1] : NULL).', ';
        }
        $sqlGroupBy = mb_substr($sqlGroupBy,0,-2);
      }


      /* LIMIT */
      if (isset($this->limit)) {
        $sqlLimit = 'LIMIT '.implode(',',$this->limit);
      }
    

      $sql = 'SELECT '.$sqlSelect.' '.$br;
      $sql .= "FROM (".implode(', ',$this->from).") ".$br; // die klammern sind hier bei einer tabelle natürlich optional..
      $sql .= $sqlGroupBy.' '.$br;
      $sql .= $sqlWhere.' '.$br;
      $sql .= $sqlGroupBy.' '.$br;
      $sql .= $sqlOrderBy.' '.$br;
      $sql .= $sqlLimit;

      $this->sql = $sql;
    }
    /* hier ist $sql definiert */
    
    return $this->db->fetch($sql);
  }

  
}

/* 
 * $sqlQuery->UPDATE('table');
 * $sqlQuery->SET(array(
 *     
 *   ));
 */
?>