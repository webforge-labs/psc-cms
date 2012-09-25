<?php

namespace Psc\Code\Compile;

use Psc\Data\Column;

/**
 * 
 */
class Tabletable5061740287bee {
  
  /**
   * @var array
   */
  protected $columns = array();
  
  /**
   * @return array
   */
  public function getColumns() {
    return $this->columns;
  }
  
  /**
   * @param array $columns
   */
  public function setColumns(Array $columns) {
    $this->columns = $columns;
    return $this;
  }
  
  /**
   * @param integer $key 0-based
   * @return Psc\Data\Column|NULL
   */
  public function getColumn($key) {
    return array_key_exists($key, $this->columns) ? $this->columns[$key] : NULL;
  }
  
  /**
   * @param Psc\Data\Column $column
   * @chainable
   */
  public function addColumn(Column $column) {
    $this->columns[] = $column;
    return $this;
  }
  
  /**
   * @param Psc\Data\Column $column
   * @chainable
   */
  public function removeColumn(Column $column) {
    if (($key = array_search($column, $this->columns, TRUE)) !== FALSE) {
      array_splice($this->columns, $key, 1);
    }
    return $this;
  }
  
  /**
   * @param Psc\Data\Column $column
   * @return bool
   */
  public function hasColumn(Column $column) {
    return in_array($column, $this->columns);
  }
}
?>