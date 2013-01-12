<?php

namespace Psc\PHPExcel;

use PHPExcel_Cell;

class RangeReadFilter implements \PHPExcel_Reader_IReadFilter {
  
  protected $firstColumn;
  protected $lastColumn;
  protected $firstRow;
  protected $lastRow;
  
  protected $range;
  
  public function __construct($column, $row) {
    if (is_array($column)) { 
      list($this->firstColumn,$this->lastColumn) = $column;
    } else {
      $this->firstColumn = 'A';
      $this->lastColumn = $column;
    }
    
    if (is_array($row)) { 
      list($this->firstRow,$this->lastRow) = $row;
    } else {
      $this->firstRow = 1;
      $this->lastRow = $row;
    }
    
    $endIndex = PHPExcel_Cell::columnIndexFromString($this->lastColumn)-1;
    for ($cellIndex = PHPExcel_Cell::columnIndexFromString($this->firstColumn)-1;
         $cellIndex <= $endIndex;
         $cellIndex++) {
      $this->range[ PHPExcel_Cell::StringFromColumnIndex($cellIndex) ] = TRUE;
    }
  }
  
  public function getRange() {
    return $this->range;
  }
  
  /**
   * Should this cell be read?
   * 
   * @param 	$column		String column index
   * @param 	$row			Row index
   * @param	$worksheetName	Optional worksheet name
   * @return	boolean
   */
  public function readCell($column, $row, $worksheetName = '') {
    
    return ($row >= $this->firstRow && $row <= $this->lastRow &&
            array_key_exists($column, $this->range));
  }
}