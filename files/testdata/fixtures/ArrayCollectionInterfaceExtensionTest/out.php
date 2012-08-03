<?php

namespace Psc\Code\Compile;

use Psc\Data\Column;

/**
 * 
 */
class Tabletable501c21ea512f3 {
  
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