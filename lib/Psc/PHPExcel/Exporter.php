<?php

namespace Psc\PHPExcel;

use
  \PHPExcel_Cell,
  \PHPExcel_NamedRange,
  \PHPExcel_Worksheet,
  \PHPExcel_Worksheet_Row,
  \PHPExcel_IOFactory,
  \PHPExcel,
  \PHPExcel_Writer_Excel2007,
  \Webforge\Common\System\File,
  \Webforge\Common\String AS S,
  \Psc\Code\Code,
  \Psc\A
;

/**
 */
abstract class Exporter extends \Psc\OptionsObject {
  
  protected $processListener;
  
  /**
   * @var \Webforge\Common\System\File
   */
  protected $exportFile;
  
  /**
   * @var PHPExcel
   */
  protected $excel;
  
  /**
   * @var PHPExcel_Worksheet
   */
  protected $sheet;

  /**
   * @var PHPExcel_Worksheet_Row
   */
  protected $row;
  
  protected $init;
  
  protected $exportType = 'xlsx';


  /**
   * @var array key name der Spalte, value: list(SpaltenBuchstabe (A,B,C,...), $label)
   */
  protected $columns;

  public $log;

  public function __construct($exportFile, $exportType = 'xlsx') {
    $this->exportType = Code::dvalue($exportType,'xlsx','xls');
    
    $this->exportFile = new File($exportFile);
    
    $this->excel = new PHPExcel();
    $this->excel->removeSheetByIndex(0);
  }
  
  protected function createRowFromArray(Array $row) {
    
    foreach ($row as $column => $value) {
      if (!array_key_exists($column, $this->columns)) {
        throw new \Psc\Exception('Request eines unbekannten Spalten-Typs: "'.$column.'" '.print_r(A::keys($row),true).'. Wurde dieser mit addColumn() hinzugefügt?');
      }
      $cell = $this->sheet->setCellValue($this->columns[$column][0].$this->row->getRowIndex(),$value,true);
    }
  }
  
  /**
   * Setzt this->sheet auf das gerade erstellte Sheet
   * und die Row auf Zeile 1
   */
  protected function createSheet($name) {
    $name = \Psc\PHPExcel\Helper::sanitizeSheetTitle($name);
    $this->excel->addSheet(new PHPExcel_Worksheet($this->excel, $name));
    $this->excel->setActiveSheetIndexByName($name);
    $this->sheet = $this->excel->getActiveSheet();
    
    $this->setRow(1);
  }
  
  /**
   * @param string $cell kann entweder A1 sein, oder A{c} wobei dann {c} mit this->row->getRowIndex() ersetzt wird (also aktuelle Zeile)
   * @return PHPExcelCell
   */
  public function setValue($cell, $value) {
    $cell = str_replace('{c}',$this->row->getRowIndex(),$cell);
    $cell = $this->sheet->setCellValue($cell, $value, true);
  }
  
  public function setValues($cellStart, Array $values, $direction = 'down') {
    if ($direction != 'down' && $direction != 'up' && $direction != 'right') {
      throw new Exception('Ich kann noch nichts anderes außer down, up und right');
    }

    $cell = $this->sheet->getCell($cellStart);
    $startRow = $cell->getRow();
    $startIndex = $cell->getColumn();
    
    if ($direction == 'down') {
      foreach ($values as $value) {
        $this->sheet->setCellValue($startIndex.($startRow++), $value);
        $this->log .= 'down: '.$startIndex.$startRow.' : '.$value."\n";
      }
    }

    if ($direction == 'up') {
      foreach ($values as $value) {
        $this->sheet->setCellValue($startIndex.($startRow--), $value);
      }
    }
    
    if ($direction == 'right') {
      foreach ($values as $value) {
        $this->sheet->setCellValue(($startIndex++).$startRow, $value); 
      }
    }
    return $this;
  }

  protected function nextRow() {
    if (isset($this->row))
      $index = $this->row->getRowIndex()+1;
    else
      $index = 1;
    
    return $this->setRow($index);
  }
  
  protected function setRow($index = 1) {
    $this->row = new PHPExcel_Worksheet_Row($this->sheet,$index);
    return $this->row;
  }

  /**
   * @param float $width in PT
   */
  public function addColumn($name, $columnChar, $label= NULL, $width = NULL) {
    if (!isset($label)) $label = $name;
    $this->columns[$name] = array($columnChar,$label, $width);
    return $this;
  }

  protected function createStandardHeader($index = 1) {
    $this->setRow($index);
    foreach ($this->columns as $item) {
      list ($columnChar, $label, $width) = $item;
      $this->setValue($columnChar.'{c}', $label);
      
      if ($width != NULL) {
        $this->setColumnWidth($columnChar, $width);
      }
    }
    $this->nextRow();
  }

  /**
   * Sollte erst nach create() ausgeführt werden
   * 
   */
  public function styleColumn($char, Array $style) {
    $this->sheet->getStyle($char.'1:'.$char.max(2,$this->row->getRowIndex()))
      ->applyFromArray($style);
  }

  public function setColumnWidth($char, $width) {  
    $this->sheet->getColumnDimension($char)
      ->setAutoSize(FALSE)
      ->setWidth($width);
  }
  
  public function download() {
    
    if ($this->exportType == 'xlsx') {
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="'.$this->getExcelFileName().'.xlsx"');
      header('Cache-Control: max-age=0');

      $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
      //$objWriter->setOffice2003Compatibility(true);
      
    } elseif($this->exportType == 'xls') {
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment;filename="'.$this->getExcelFileName().'.xls"');
      header('Cache-Control: max-age=0');

      $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
    }
    
    $objWriter->save('php://output');
  }
  
  public function write($filename) {
    if ($this->exportType == 'xlsx') {
      $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
    } elseif($this->exportType == 'xls') {
      $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
    } else {
      throw new \Psc\Exception('unbekannter ExportType: '.Code::varInfo($this->exportType));
    }
    
    $objWriter->save($filename);
    $this->fireProcess(100);
    return $filename;
  }
  
  public function getExcelFileName() {
    return $this->exportFile->getName(File::WITHOUT_EXTENSION);
  }
  
  
  /**
   * @return PHPExcel_Cell
   */
  protected function nextColumn(PHPExcel_Cell $cell) {
    $columnIndex = PHPExcel_Cell::columnIndexFromString($cell->getColumn());
    return $this->sheet->getCellByColumnAndRow($columnIndex+1, $cell->getRow()->getRowIndex());
  }


  /**
   * @param StyleTheme
   * @return StyleTheme
   */
  public function setStyleTheme(StyleTheme $theme = NULL) {
    if (!isset($theme)) $theme = new StandardStyleTheme($this->excel);
    
    $this->theme = $theme;
    return $this->theme;
  }


  public function fireProcess($value) {
    if (isset($this->processListener) && $this->processListener instanceof \Closure) {
      $f = $this->processListener;
      $value = min(100,max(0,$value)); // constrain between 0 and 100
      
      $f((int) round($value));
    }
  }
}