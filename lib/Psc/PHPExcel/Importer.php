<?php

namespace Psc\PHPExcel;

use \PHPExcel_IOFactory,
    \Psc\SimpleObject,
    \PHPExcel_Worksheet_Row,
    \Psc\Code\Code
    ;

abstract class Importer extends \Psc\OptionsObject {

  /**
   * @var string
   */
  protected $importFile;
  

  protected $excel;
  
  /**
   * @var PHPExcel_Worksheet
   */
  protected $sheet;
  
  protected $reader;
  
  protected $init;
  
  public $log;
  
  /**
   * @var PHPExcel_Worksheet_Row
   */
  protected $currentRow;
  
  public function __construct($importFile) {
    $this->importFile = $importFile;
    
    $this->setDefaultOptions(array(
      'maxColumn'=>'P',
      'maxRow'=>2500,
      'minRow'=>0,
      'worksheet'=>'Tabelle1',
    ));
    
    $this->log = NULL;
    
    $this->reader = PHPExcel_IOFactory::createReaderForFile($this->importFile);
    $this->setUp();
  }
  
  public function setUp() {
    
  }

  public function init() {
    $ws = $this->getOption('worksheet');
    
    /* Reader und so */
    $this->reader->setReadFilter( new RangeReadFilter($this->getOption('maxColumn'), $this->getOption('maxRow')));
    //$this->reader->setLoadSheetsOnly(array($ws));  // ich glaub das geht nicht bei numeric
    
    $this->excel = $this->reader->load($this->importFile);
    
    if (is_numeric($ws)) {
      $this->sheet = $this->excel->getSheet($ws);
    } else {
      $this->sheet = $this->excel->getSheetByName($ws);
    }
    
    if ($this->sheet == NULL) {
      throw new \Psc\Exception('Worksheet: '.Code::varInfo($this->getOption('worksheet')).' nicht gefunden.');
    }
    
    $this->init = TRUE;
    return $this;
  }

  /**
   * Führt für alle Zeilen des Dokumentes processRow() aus
   */
  public function process() {
    foreach ($this->sheet->getRowIterator() as $row) {
      if ($row->getRowIndex() < $this->getOption('minRow')) {
        $this->log .= 'Skip.minRow: '.$row->getRowIndex()."\n";
        continue;
      }
      
      if ($row->getRowIndex() > $this->getOption('maxRow')) {
          $this->log .= 'Break.maxRow: '.$row->getRowIndex()."\n";
          break;
      }
      
      $this->log .= 'processRow('.$row->getRowIndex().")\n";
      $this->currentRow = $row;
      $this->processRow($row);
    }
  }
  
  abstract protected function processRow(PHPExcel_Worksheet_Row $row);
  
  
  protected function getCell($char) {
    return $this->sheet->getCell($char.$this->currentRow->getRowIndex());
  }
  
  protected function getStyle($char) {
    return $this->sheet->getStyle($char.$this->currentRow->getRowIndex());
  }
  
  protected function getRow() {
    return $this->currentRow;
  }
  
  
  public function setWorksheet($sheet) {
    $this->setOption('worksheet',$sheet);
    return $this;
  }
  
  public function getWorksheet() {
    return $this->getOption('worksheet');
  }

  /**
   * @return DataInput
   */
  public function getData() {
    if (!$this->init) {
      throw new \Psc\Exception('init() wurde noch nicht ausgeführt.');
    }
      
    
    return $this->data;
  }
}