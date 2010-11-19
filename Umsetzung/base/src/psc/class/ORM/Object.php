<?php

class ORMObject extends Object {

  protected $id;

  protected $loaded = FALSE;
  protected $table;

  protected $con = NULL;

  public function __construct($id = NULL) {
    if (isset($id))
      $this->fetch($id);
  }

  /**
   * 
   * @chainable
   */  
  public function fetch($id = NULL) {
    if (isset($id)) $this->setId($id); // forceId passiert in setId

    $sql = new SQLQuery();
    $sql->SELECT($this);
    $sql->WHERE('id',$this->id);
    
    $res = $sql->result();
         
    if (count($res) != 1) // > 1 ist ja eh weired
      throw new Exception('Kein Datensatz mit der Id: '.$this->id);

    $row = $res[0];

    $this->init($row);
    return $this;
  }

  /**
   * Initialisiert das Objekt mit einer Zeile eines SQL Results
   * @param array $row es Objekt versucht seine Werte mit $row[ $table.':'.$column ] zu lesen (siehe prefix)
   * @param string $prefix wenn angegeben versucht das Objekt seine Werte mit $row[ $prefix.':'.$table.':'.$column ] zu erreichen
   * @chainable
   */
  public function init(Array $row, $prefix = NULL) {
    if (isset($prefix)) $prefix .= ':';

    foreach(ORM::getColumns($this) as $c => $column) {
      $this->$c = $this->ORMvalue($row[ $prefix.$this->table.':'.$c ], $column);
    }
    
    $this->loaded = TRUE;

    return $this;
  }

  /**
   * Gibt den PHP Wert einer Spalte zurück
   * 
   * 
   * der Hook ist: $this->ORMvalue{$columnName}
   * @param mixed $value der Wert der aus dem Result der Datenbank zurückkommt
   * @param array $column die Spezifikation der ORM column siehe ORM::getColumn()
   * @hookable
   */
  protected function ORMvalue($value, Array $column) {
    if ($this->hasMethod($f = 'ORMvalue'.$column['name'])) {
      return $this->$f($value,$column);
    }

    /* wert ist NULL, wenn NULL erlaubt ist */
    if ($value === NULL && $column['null'] == TRUE) 
      return NULL;

    switch ($column['type']) {
      default:
        Debug::notice('unbekannter Typ: '.$column['type'].' aus der ORM Column');
        return $value;

      case ORM::STRING:
        return $value;
        
      case ORM::INT:
        return (int) $value;

      case ORM::BOOL:
        return ($v == 1); // dies geht auch mit '1'

      case ORM::FLOAT:
        return floatval($v); // wandelt string 999.9384 in float um
          
      case ORM::VALUES:
        if (in_array($v,$column['parameters']))
        return $v; // da dies hier eine value aus den möglichen ist

    }
  }

  public function getTable() {
    /* erlaubt einen eigenen Tabellennamen zu setzen */
    if (!isset($this->table))
      $this->table = ORM::getTable($this);

    return $this->table;
  }

  public function setId($id) {
    $this->id = Code::forceId($id);
    return $this;
  }
}

?>