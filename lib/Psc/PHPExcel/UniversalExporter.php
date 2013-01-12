<?php

namespace Psc\PHPExcel;

use \PHPExcel_Worksheet_Row,
    \Psc\DataInput
;

/**
 *
 */
class UniversalExporter extends Exporter implements Transporter {
  
  protected $columnMapping;

  /**
   * @var DataInput
   */
  protected $data;
  
  public function __construct($exportFile, $exportType = 'xlsx') {
    parent::__construct($exportFile, $exportType);
    
    $data = array();
    $this->data = new DataInput($data);
    $this->columnMapping = array();
  }
  
  public function create() {
    $this->createPage($this->data);
    
    return $this;
  }
  
  protected function createPage(DataInput $data) {
    $this->createSheet('Tabelle1');
    
    $header = FALSE;
    foreach ($this->columnMapping as $c) {
      list($char, $key, $label) = $c;
      
      $name = implode('-',$key);
      if ($label != NULL) {
        $header = TRUE;
      }
      
      $this->addColumn($name, $char, $label);
      $this->sheet->getColumnDimension($char)->setAutoSize(TRUE);
    }

    if ($header)
      $this->createStandardHeader();
    
    $rows = $data->get(array('rows'), array(), array());
    foreach ($rows as $rowIndex => $row) {
      $this->processRow($row);
      $this->nextRow();
    }
  }


  /**
   * @param char $excelCellColumn nur der Buchstabe der Spalte (A ist die erste)
   * @param Array $key die keys die aus dem  Datainput gelesen werden vor jeden $key wird array('rows',$rowIndex) eingefügt
   * @param string $label das Label für den Header, wenn NULL gibt es keinen Header
   */
  public function addColumnMapping($excelCellColumn, Array $key, $label = NULL, $size = NULL) {
    $this->columnMapping[] = array($excelCellColumn, $key, $label, $size);
  }
  
  public function processRow(Array $row) {
    $this->createRowFromArray($row);
  }
  
  protected function filterValue($value) {
    $f = $this->getOption('valueFilter');
    
    if ($f instanceof \Closure) {
      return $f($value);
    } else {
      return $value;
    }
  }
  
  public function setValueFilter(\Closure $f) {
    $this->setOption('valueFiler',$f);
    return $this;
  }
  
  public function addTrimFilter() {
    $this->setValueFilter(function($value) {
      if (($value = trim($value)) != '') {
      return $value;
    }
    });
  }
}