<?php

namespace Psc\PHPExcel;

use Webforge\Common\System\File;

use PHPExcel_IOFactory;
use PHPExcel_Reader_IReader AS ExcelReader;
use PHPExcel_Worksheet AS ExcelSheet;
use PHPExcel_Cell AS ExcelCell;
use PHPExcel_Cell_DataType AS ExcelDataType;
use Psc\PHPExcel\Helper AS h;
use Psc\Exception AS DefaultException;

class SimpleImporter {
  
  protected $reader, $file;
  
  protected $useFirstLineAsColumns = TRUE;
  protected $columnsMapping = NULL;
  protected $sheets = array();
  
  public function __construct(File $excel, ExcelReader $reader) {
    $this->reader = $reader;
    $this->file = $excel;
  }
  
  public static function createFromFile(File $excel) {
    $reader = PHPExcel_IOFactory::createReaderForFile((string) $excel);
    
    return new static($excel, $reader);
  }
  
  public function useFirstLineAsColumns() {
    $this->useFirstLineAsColumns = TRUE;
    return $this;
  }
  
  /**
   * Adds a sheet to be selected with findAll() by index
   *
   * @param int $index
   */
  public function selectSheetByIndex($index) {
    $this->sheets[] = (int) $index;
    return $this;
  }

  /**
   * Adds a sheet to be selected with findAll()
   * 
   * @param string or int if int this is the index if string this is the name of the sheet
   */
  public function selectSheet($name) {
    $this->sheets[] = $name;
    return $this;
  }
  
  /**
   * Returns all selected Sheets with all rows
   *
   * @return Sheet[]
   */
  public function findAll() {
    $sheets = array();
    
    foreach ($this->readSelectedSheets() as $selectName => $excelSheet) {
      $sheets[$selectName] = new Sheet($selectName, $this->readRows($excelSheet));
    }
    
    return $sheets;
  }
  
  protected function readRows(ExcelSheet $excelSheet) {
    if ($this->useFirstLineAsColumns) {
      $mapping = $this->readFirstLineNumeric($excelSheet);
      
      return $this->readRowsWithMapping($excelSheet, $mapping, 2);
      
    } else {
      throw new DefaultException('only useFirstLineAsColumns is implemented, yet');
    }
  }
  
  /**
   * Returns the line as numeric array with the number 1 from the sheet
   * 
   * @return array
   */
  protected function readFirstLineNumeric(ExcelSheet $excelSheet) {
    $lines = $this->readRowsNumeric($excelSheet, 1, 1);
    
    return $lines[1];
  }

  /**
   * @return array[array]
   */
  protected function readRowsNumeric(ExcelSheet $excelSheet, $minRow, $maxRow = NULL) {
    $data = array();
    $nullValue = NULL;
    $maxCell = h::getColumnIndex($excelSheet->getHighestDataColumn());
    $emptyRow = array_fill(0, $maxCell, $nullValue);
    
    foreach ($excelSheet->getRowIterator($minRow) as $rowNum => $wsRow) {
      if ($maxRow !== NULL && $rowNum > $maxRow) break;
      
      $row = array();
      foreach ($wsRow->getCellIterator() as $cell) {
        $cellIndex = h::getColumnIndex($cell->getColumn());
        
        $isEmpty = TRUE;
        $row[$cellIndex] = $this->readValue($cell, $isEmpty, $nullValue);
      }
    
      ksort($row);
      $data[$rowNum] = array_replace($emptyRow, $row);
    }
    return $data;
  }
  
  protected function readRowsWithMapping(ExcelSheet $excelSheet, Array $mapping, $minRow = 1, $maxRow = NULL) {
    $nullValue = NULL;
    $emptyRow = array_combine(
      array_values($mapping),
      array_fill(0, count($mapping), $nullValue)
    );
    
    $rows = array();
    foreach ($excelSheet->getRowIterator($minRow) as $rowNum => $wsRow) {
      if ($maxRow !== NULL && $rowNum > $maxRow) break;

      $row = array();
      foreach ($wsRow->getCellIterator() as $cell) {
        $cellIndex = h::getColumnIndex($cell->getColumn());
      
        $isEmpty = TRUE;
        $row[$mapping[$cellIndex]] = $this->readValue($cell, $isEmpty, $nullValue);
      }
      
      $rows[$rowNum] = array_replace($emptyRow, $row);
    }
    
    return $rows;
  }
  
  protected function readValue(ExcelCell $cell, &$isEmpty, $defaultValue = NULL) {
    $rawValue = $cell->getValue();
    $type = $cell->getDataType();
    
    switch($type) {
      case ExcelDataType::TYPE_NULL:
        $isEmpty = TRUE;
        return $defaultValue;
      
      case ExcelDataType::TYPE_STRING2:
			case ExcelDataType::TYPE_STRING:
			case ExcelDataType::TYPE_INLINE:
        $value = (string) $rawValue;
        
        if ($this->isEmptyString($value)) {
          $isEmpty = TRUE;
          return $defaultValue;
        }
        break;
        
      case ExcelDataType::TYPE_NUMERIC:
        $value = (int) $rawValue;
        break;
        
      case ExcelDataType::TYPE_FORMULA:
        throw new DefaultException('Cannot read formular yet from cell, because getCalculatedValue is deprecated somehow. Please replace formula with value explicit.');
        break;
      
      case ExcelDataType::TYPE_BOOL:
        $value = (bool) $rawValue;
        break;
        
      default:
      case ExcelDataType::TYPE_ERROR:
        throw new DefaultException('The DataType from Cell cannot be switched: '.$type);
    }
    
    $isEmpty = FALSE;
    return $value;
  }
  
  protected function isEmptyString($string) {
    return trim($string) === '';
  }
  
  protected function readSelectedSheets() {
    $sheets = array();
    $excel = $this->getExcel();
    foreach ($this->sheets as $select) {
      if (is_integer($select)) {
        $sheets[$select] = $excel->getSheet($select);
      } else {
        $sheets[$select] = $excel->getSheetByName((string) $select);
      }
    }
    return $sheets;
  }
  
  protected function getExcel() {
    if (!isset($this->excel)) {
      $this->excel = $this->reader->load((string) $this->file);
    }
  
    return $this->excel;
  }
}
?>