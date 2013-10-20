<?php

namespace Psc\Data;

use Webforge\Types\Type;
use Webforge\Common\ArrayUtil AS A;

/**
 * 
 */
class Table extends \Psc\SimpleObject {
  
  const END = A::END;
  
  /**
   * @var array
   */
  protected $columns;
  
  /**
   * @var array
   */
  protected $rows;
  
  public function __construct(Array $columns, Array $rows = array()) {
    $this->setColumns($columns);
    $this->setRows($rows);
  }
  
  public static function Column($name, $type, $label = NULL) {
    if (is_string($type)) {
      $type = Type::create($type);
    }
    return new Column($name, $type, $label);
  }
  
  public function insertRow(Array $row, $index = self::END) {
    if (!is_integer($index) && $index !== self::END) {
      throw new \InvalidArgumentException('index muss ein integer sein. self::END f?r append');
    }
    
    if (!count($row) === count($this->columns)) {
      throw new \InvalidArgumentException("Zeile hat nicht %d Spalten sondern %d Spalten.", count($row), count($this->columns));
    }
    
    A::insert($this->rows, $row, $index);
    return $this;
  }
  
  /**
   * @param array $columns
   */
  public function setColumns(Array $columns) {
    $this->columns = $columns;
    return $this;
  }
  
  /**
   * @return array
   */
  public function getColumns() {
    return $this->columns;
  }
  
  /**
   * @param array $rows
   */
  public function setRows(Array $rows) {
    $this->rows = array();
    foreach ($rows as $row) {
      $this->insertRow($row);
    }
    return $this;
  }
  
  /**
   * @return array
   */
  public function getRows() {
    return $this->rows;
  }
}
?>