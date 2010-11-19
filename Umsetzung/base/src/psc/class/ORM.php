<?php

class ORM extends Object {

  const INT = 'INT';
  const STRING = 'STRING';
  const BOOL = 'BOOL';
  const UTS = 'UTS';
  const VALUES = 'VALUES';
  const FLOAT = 'FLOAT';

  /**
   * Der Supercache für alle ORM Objekte
   * 
   * alle ORM Objekte die mit factory() oder new() erstellt werden, schreiben sich hier in diesen Cache. Dies geschieht mit cache().
   * cache() wird von __construct() aufgerufen, damit das Objekt entscheiden kann ob es überhaupt jemals gecached werden soll.
   * nur bei ORM::factory() wird dieser Cache auch gelesen. mit new $className() erstellte Objekte können nicht aus dem Cache gelesen werden
   * @var array
   */
  protected static $objectsCache;

  protected static $cacheTableNames;
  protected static $cacheColumns;

  /**
   * 
   * @param string
   * @param int $id
   */
  public static function factory($objectName, $id) {
    $id = Code::forceId($id);
    
    if (isset(self::$cacheTableNames[$objectName]))
      $table = self::$cacheTableNames[$objectName];
    else {
      $o = new $objectName();
      $table = self::$cacheTableNames[$objectName] = $o->getTable();
      unset($o);
    }

    /* kann sein, dass wir hier vergessen $con zu berücksichtigen?
       eventuell müsste die instanz vom objekt hier auch nach $con gefragt werden ENHC
     */
    if (isset(self::$objectsCache[ $table ][ $id ]))
      return self::$objectsCache[ $table ][ $id ];
    else 
      return new $objectName($id); 
  }


  /**
   * Gibt die Spalten für ein bestimmtes Objekt zurück
   * 
   * name => Name der Spalte
   * type => eine ORM Type-Klassenkonstante
   * parameters => die Parameter zum ORM-Type
   * null => ob die Spalte NULL in der Datenbank sein darf
   */
  public static function getColumns(ORMObject $o) {
    /* cache hit */
    if (isset(self::$cacheColumns[ $o->getCon() ][ $o->getClass() ]))
      return self::$cacheColumns[ $o->getCon() ][ $o->getClass() ];

    $model = DBModel::instance($o->getCon()); // wir nehmen die gewünschte Connection aus dem Object

    $cols = array();
    foreach ($model->getColumns($o->getTable()) as $column) {
      $col = array();
      
      $col['name'] = $column['name'];
      $col['null'] = $column['null'];
      
      list($col['type'], $col['parameters']) = self::mapMySQLType($column);
      
      $cols[$column['name']] = $col;
    }

    self::$cacheColumns[ $o->getCon() ][ $o->getClass() ] = $cols;
    return $cols;
  }

  /**
   * Gibt den Tabellennamen für ein Objekt zurück
   * 
   * Diese Funktion soll nur einmal von ORMObject benutzt werden. 
   * Danach sollte um den Namen einer Tabelle für ein Objekt herauszubekommen immer eine Instanz des Objektes befragt werden
   * @return string
   */
  public static function getTable(ORMObject $o) {
    return Inflector::plural($o->getClass());
  }


  public static function cache(ORMObject $o) {
    if (!array_key_exists($table = $o->getTable(),self::$objectsCache))
      self::$objectsCache[$table] = array();

    self::$objectsCache[$table][$o->getId()] =& $o;
  }


  /**
   * 
   * wird von SQLQuery aufgerufen, wenn select($object) aufgerufen wurde.
   * wir fügen hier alle wichtigen infos für das Objekt dem SQLQuery hinzu
   * TODO: im moment sind noch keine automatischen left-joins möglich
   */
  public static function select(SQLQuery &$sql, ORMObject $o) {
    $t = $o->getTable();

    /* hier vielleicht nicht immer in from eintragen bei left joins? */
    $sql->FROM($t);

    foreach (self::getColumns($o) as $column) {
      $sql->SELECT(
        array(
          'table'=>$t,
          'column'=>$column['name'], 
          'alias'=>$t.':'.$column['name']
        )
      );
    }

    return $sql;
  }

  /**
   * Erstellt den ORMType für ein Mysql Feld
   * 
   * Gibt den Typ für ein Feld für einen MySQLType zurück.
   * der erste Parameter ist die Klassenkonstante, der zweite ist ein parameter mit eventuellen optionen;
   * 
   * Folgende zusätzliche Optionen werde zurückgegeben:
   * FLOAT: array('length'=>int, 'decimals'=>int, 'unsigned'=>bool)
   * INT: array('unsigned'=>bool)
   * VALUES: string[]
   * STRING: int|NULL
   * UTS: date|datetime|time|timestamp|year
   * 
   * @todo language string support?
   * @param array $mysqlType
   * @return list(string type,mixed options)
   */
  protected static function mapMySQLType(Array $mysqlType) {
    $mType = mb_strtolower($mysqlType['type']); 

    $unsigned = isset($mysqlType['flags']) && mb_strpos((string) $mysqlType['flags'], 'unsigned') !== FALSE;

    switch ($mType) {
      case 'smallint':
      case 'mediumint':
      case 'bigint':
      case 'int':
      case 'integer':
        return array(self::INT,array('unsigned'=>$unsigned));

      case 'tinyint': // bool check
        if (a::size($mysqlType['parameters']) >= 1 && $mysqlType['parameters'][0] == 1 && $unsigned) // length == 1 && unsigned
          return array(self::BOOL,NULL);
        else
          return array(self::INT,array('unsigned'=>$unsigned));
    
      case 'real':
      case 'double':
      case 'float':
      case 'decimal':
      case 'numeric':
        return array(self::FLOAT,array('length'=>(int) $mysqlType['parameters'][0], 'decimals'=>(int) $mysqlType['parameters'][1], 'unsigned'=>$unsigned));

      case 'char':
      case 'varchar':
      case 'binary':
      case 'varbinary': 
        return array(self::STRING,
          (count($mysqlType['parameters']) == 1) ? (int) $mysqlType['parameters'][0] : NULL);
      
      case 'date':
      case 'datetime':
      case 'time':
      case 'year':
      case 'timestamp':
        return array(self::UTS,$mType);
      
      case 'tinyblob':
      case 'blob':
      case 'mediumblob':
      case 'longblob':
      case 'tinytext':
      case 'text':
      case 'mediumtext':
      case 'longtext':
        return array(self::STRING,NULL);

      case 'enum':
      case 'set':
        return array(self::VALUES,$mysqlType['parameters']);
    }

    throw new Exception('Datentyp '.$mType.' ist unbekannt');
  }

}

?>