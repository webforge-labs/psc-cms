<?php

namespace Psc\PHPExcel;

use \PHPExcel_Worksheet_Row,
    \Psc\DataInput
;

/**
 *
 *  class bla extends UniversalImporter {
 *    public function setUp() {
 *      $this->addColumnMapping('A', array('oid'));
 *      $this->addColumnMapping('B', array('label'));
 *       
 *      $this->addTrimFilter();
 *      $this->filterEmptyRows();
 *    }
 *
 *    **
 *     * Gibt ein DataInput mit den gesetzten daten zurück, nachdem init() und process() aufgerufen wurden
 *     *
 *    public function getData() {
 *      return parent::getData();
 *    }
 *  }
 */
class UniversalImporter extends Importer implements Transporter {
  
  protected $columnMapping = array();
  
  /**
   * @var DataInput
   */
  protected $data;
  
  public function __construct($importFile) {
    parent::__construct($importFile);
    
    $data = array();
    $this->data = new DataInput($data);
  }
  
  public function processRow(PHPExcel_Worksheet_Row $row) {
    $data = array();
    $rowData = new DataInput($data);
    $empty = TRUE;
    
    foreach ($this->columnMapping as $c) {
      list($char, $key) = $c;
      
      $cell = $this->getCell($char);
      $value = $cell->getValue();
      if ($value instanceof \PHPExcel_RichText) {
        $value = $value->getPlainText();
      }
      
      $style = $this->getStyle($char);
      $value = $this->filterValue($value, $style);
      
      if ($value != NULL) {
        $empty = FALSE;
      }
      $rowData->setDataWithKeys($key,$value);
      
      $this->processedColumn($c, $cell, $style, $value, $empty, $rowData, $row);
    }
    
    if (!$empty || $this->getOption('filterEmptyRow',FALSE) == FALSE) {
      $this->data->setDataWithKeys(array('rows',$row->getRowIndex()), $rowData->toArray());
    } else {
      $this->log .= 'Skipped.Empty: '.$row->getRowIndex()."\n";
    }
  }
  
  /**
   * Ein hook bevor überprüft wird ob die Spalte leer ist etc
   *
   * kann direkt rowData setzen und z.b. empty verändern
   */
  protected function processedColumn(Array $c, \PHPExcel_Cell $cell, \PHPExcel_Style $style, &$value, &$empty, \Psc\DataInput $rowData, \PHPExcel_Worksheet_Row $row) {
  }
  
  /**
   * Führt den Import aus und gibt die Zeilen zurück
   */
  public function importRows() {
    $this->init();
    $this->process();
    return $this->getData()->get('rows');
  }
  
  /**
   * 
   * Die Parameter sind hier größtenteils disfunktional, aber es geht um das Transporter-Interface
   * @param char $excelCellColumn nur der Buchstabe der Spalte (A ist die erste)
   * @param Array $key die keys die auf das Datainput angewendet werden for jeden $key wird array('rows',$rowIndex) eingefügt
   */
  public function addColumnMapping($excelCellColumn, Array $key, $label = NULL, $size = NULL) {
    $this->columnMapping[] = array($excelCellColumn, $key);
  }
  
  protected function filterValue($value, $style) {
    $f = $this->getOption('valueFilter');
    
    if ($this->getOption('removeStrikeThrough',FALSE)) {
      if ($style->getFont()->getStrikeThrough() == TRUE) {
        $this->log .= 'Value war durchgestrichen. => NULL '."\n";
        return NULL;
      }
    }
      
    if ($f instanceof \Closure) {
      return $f($value);
    } else {
      return $value;
    }
  }
  
  public function setValueFilter(\Closure $f) {
    $this->setOption('valueFilter',$f);
    return $this;
  }
  
  public function addTrimFilter() {
    $this->setValueFilter(function($value) {
      if (is_string($value)) {
        $value = str_replace('\n', "\n", $value);
        $value = trim($value);
       
        if ($value == '') {
          return NULL;
        }
      }
      
      return $value;
    });
  }
  
  public function filterEmptyRows($status = TRUE) {
    $this->setOption('filterEmptyRow',(bool) $status);
    return $this;
  }
  
  public function convertDateToPHP($excelValue) {
//    \PHPExcel_Shared_Date::$dateTimeObjectType = 'Psc\DateTime\DateTime';
    return new \Psc\DateTime\DateTime(\PHPExcel_Shared_Date::ExcelToPHP($excelValue));
  }
}