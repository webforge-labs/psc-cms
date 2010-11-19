<?php


class DBModel extends Object {
  
  /* 
   * const ONE_TO_ONE = '1:1';
   * const ONE_TO_MANY = '1:n';
   * const MANY_TO_ONE = 'n:1';
   * const MANY_TO_MANY = 'n:n';
   */

  protected static $instances = array();
  
  /**
   * Informationen über die Tabellen der Datenbank 
   *
   * - Beziehungen zu anderen Tabellen
   * - Singular/Plural Name
   * - primärschlüssel
   * - fremdschlüssel
   * - Klassenname
   * @var array
   */
  protected $tables = array();
  
  /**
   * Informationen über die Spalten der Tabellen der Datenbank
   * 
   * - Attribute
   * @var array
   */
  protected $columns = array();

  /**
   * 
   * @var DB
   */
  protected $db;

  /**
   * Erstellt ein neues DB Model
   * 
   * Die Datenbankverbindung wird mit DB::instance() erzeugt. 
   * Es werden alle Tabelleninformationen neu erstellt, wenn diese nicht gecached sind.
   * @var string Name der Verbindung zur Datenbank
   */
  protected function __construct($con = NULL) {
    $this->db = DB::instance($con);

    $this->init();
  }

  /**
   * 
   * @return DBModel
   */
  public static function instance($con = NULL) {
    if (!isset(self::$instances[$con])) {
      self::$instances[$con] = new DBModel($con);
    }
    return self::$instances[$con];
  }


  /**
   * Lädt die Informationen der Datenbank ins Model
   * 
   */
  public function init() {
    $tableNames = $this->db->getTables();
    foreach ($tableNames as $tableName) {

      if (!isset($this->tables[$tableName]))
        $this->tables[$tableName] = array();

      $table = array(
        'plural'=>$tableName,
        'singular'=>Inflector::singular($tableName),
        'relations'=>array()
      );

      /* 
       * /\* ist die tabelle eine n2n zwischentabelle? *\/
       * if (($list = $this->isN2NTable($tableName, $tableNames)) !== FALSE) {
       *   list($table1, $table2) = $list;
       *   
       *   $this->tables[$table1]['relations'][$table2] = Array(
       *     'foreignKey' => $column['name']
       *   );
       * }
       */
      
      $columns = $this->db->getColumns($tableName);

      foreach ($columns as $column) {
        if (!isset($this->columns[$tableName][ $column['name'] ]))
          $this->columns[$tableName][ $column['name'] ] = array();

        /*
         * Column ist hier:
         * name
         * type
         * parameters
         * flags
         * null
         * primary
         */
        $column['foreignKey'] = NULL;

        /* ist die Spalte der Primärschlüssel? */
        if ($column['primary'] == TRUE) {
          $table['primary'] = $column['name'];
        }

        /* ist die Spalte ein Fremdschlüssel zu einer anderen Tabelle? */
        if (($foreignTable = $this->isForeignKey($column['name'], $tableNames)) !== FALSE) {
          $table['relations'][$foreignTable] = Array(
            'foreignKey' => $column['name']
          );
          
          /* relation-information für die andere tabelle */
          $this->tables[$foreignTable]['relations'][$tableName] = Array(
            'foreignKey' => NULL
          );
          
          $column['foreignKey'] = $foreignTable;
        }
        /* speichern, wir überschreiben keine informationen die schon gesetzt wurden */

        $this->columns[$tableName][ $column['name'] ] = array_merge($this->columns[$tableName][ $column['name'] ], $column);
      }
      
      
      $this->tables[$tableName] = array_merge($this->tables[$tableName], $table);
    }
  }

  /**
   * 
   * Überprüft ob der Spaltenname ein Fremdschlüssel für eine andere Tabelle ist
   * Gibt die Fremdtabelle zurück
   * 
   * erkennt:
   * fk_tableName_primarykey
   * tableName_primaryKey
   * @param string $columnName
   * @param array $tables ein Array von Tabellennamen, die es in der Datenbank gibt. Wenn nicht übergeben wird nicht überprüft ob die Tabelle auch existiert
   * @return string
   */
  public function isForeignKey($columnName, Array $tables = NULL) {
    $match = array();

    if (Preg::match($columnName,'/^([a-zA-Z0-9]+)_([a-zA-Z0-9_]+)/',$match) > 0) {
      $potentialTableName = $match[1];
    } elseif (Preg::match($columnName,'/^fk_([a-zA-Z0-9]+)_([a-zA-Z0-9_]+)/',$match) > 0) {
      $potentialTableName = $match[1];
    } else {
      return FALSE;
    }

    /* überprüfen ob der angebliche Tabellen Name eine Tabelle ist */
    if (isset($tables)) return in_array($potentialTableName,$tables) ? $potentialTableName : FALSE;
  }

  /**
   * Gibt die Spalteninformationen für eine Tabelle zurück
   * 
   * @return array
   */
  public function getColumns($table) {
    return $this->columns[ $table ];
  }
}

?>