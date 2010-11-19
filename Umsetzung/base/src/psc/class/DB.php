<?php
/**
 * 
 * $con ist der Name der DBConnection
 */
abstract class DB extends Object {

  protected static $driver = 'MySQL';

  /**
   * 
   * @var array
   */
  protected static $instances;

  /**
   * 
   * @var DBConnection
   */
  protected $connection;

  /**
   * Das letzte Query das ausgeführt wurde
   * 
   * @var string
   */
  protected $lastQuery;

  /**
   * Log von SQL Queries
   * 
   * @var array
   */
  protected $log;

  public function __construct($con = NULL) {
    $this->connection = DBConnection::instance($con);
  }


  /**
   * stellt ein Query an die Datenbank und gibt das Resource-Handle zurück
   *
   * registerQuery($sql) muss aufgerufen werden
   * @param string $sql
   * @return resource
   */
  abstract public function query($sql);

  /**
   * stellt eine Anfrage an die Datenbank und gibt das Ergebnis als Array zurück
   * 
   * die Funktion ist ähnlich wie die alte mysql_fetchresult. Sie kann entweder einen String oder eine Query Resource erhalten
   * die Rückgabe ist ein numerischer Array dessen Größe die Anzahl der Ergebniszeilen ist und hat auf zweiter Ebene das format 'columnname'=>'value'
   * @param string|resource $query
   * @return array SQL Result
   * @uses query()
   * @see getResultIterator()
   */
  abstract public function fetch($query);


  /**
   * Gibt aus einem Select das erste oder benannte Feld der ersten Zeile zurück
   * 
   * @param string|resource $query
   * @param string $field der Name des Feldes in der ersten Ergebniszeile
   * @uses fetch
   * @return mixed
   */
  abstract public function fetchfield($query, $field = NULL);


  public static function instance($con = NULL) {
    if (!isset(self::$instances[$con])) {
      $cn = self::getClassName();
      
      self::$instances[$con] = new $cn($con);
    }
    return self::$instances[$con];
  }


  protected function registerQuery($sql) {
    $this->lastQuery = $sql;
    $this->log[] = $sql;
  }


  /**
   * Nimmt einen Mysql Error erzeugt eine Fehlermeldung und gibt die Exception zurück
   * 
   * @param string $type der Bereich aus dem der Fehler kommt
   * @param string $errorText normalerweise der von mysql_error()
   * @param string $errorNum normalerweise die von mysql_errno()
   * @param string $sql wenn nicht gesetzt wird lastQuery genommen
   * @return Exception
   */
  protected function createException($type, $errorString, $errorNum=NULL, $sql = NULL) {
    $error = 'DB Error('.$type.'): '.$errorString;
    
    switch ($errorNum) {
    default:
      $exception = new DBException($error);
      break;
    
    case 1146: // table doesn exist
      $exception = new DBNoSuchTableException($error, $errorNum);
      break;

    case 1062: // duplicate key
      $exception = new DBDuplicateKeyException($error, $errorNum); 
      break;
    }

    
    $exception->sql = (isset($sql)) ? $sql : $this->lastQuery;

    return $exception;
  }


  /**
   * Gibt Spalteninformationen für eine Tabelle der Datenbank zurück
   * 
   * die Klasse DB{$driver}TableParser wird instanziiert und getColumns() aufgerufen
   * @return array
   */
  public function getColumns($tableName) {
    $cn = 'DB'.$this->getDriver().'TableParser';
    $tp = new $cn($this);

    return $tp->getColumns($tableName);
  }

  /**
   * Gibt alle Tabellen der Datenbank zurück
   * 
   * die Klasse DB{$driver}TableParser wird instanziiert und getTables() aufgerufen
   * @return array
   */
  public function getTables() {
    $cn = 'DB'.$this->getDriver().'TableParser';
    $tp = new $cn($this);

    return $tp->getTables();
  }

  /**
   * 
   * @return string
   */
  public static function getClassName() {
    return 'DB'.self::getDriver();
  }

  /**
   * 
   * @return string
   */
  public static function getDriver() {
    /* hier kann dann später mal eine config ausgelesen werden */
    return self::$driver;
  }
}

class DBException extends Exception { public $sql; }
class DBDuplicateKeyException extends DBException {}
class DBNoSuchTableException extends DBException {}



?>