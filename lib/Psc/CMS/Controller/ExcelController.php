<?php

namespace Psc\CMS\Controller;

use Psc\CMS\Project;
use Psc\Code\Code;
use Psc\TPL\TPL;
use Psc\TPL\Template;
use Psc\Config;
use Psc\Net\HTTP\HTTPException;
use Psc\Net\ServiceResponse;
use Psc\Net\Service;

use
  \PHPExcel_Cell,
  \PHPExcel_NamedRange,
  \PHPExcel_Worksheet,
  \PHPExcel_Worksheet_Row,
  \PHPExcel_IOFactory,
  \PHPExcel,
  \PHPExcel_Writer_Excel2007
;
use Webforge\Common\System\File;
use Psc\Form\ValidationPackage;
use Psc\PHPExcel\Helper AS h;

class ExcelController extends \Psc\SimpleObject {
  
  protected $v;
  
  public function __construct(\Psc\CMS\Project $project = NULL, ValidationPackage $validationPackage = NULL) {
    $this->v = $validationPackage ?: new ValidationPackage();
  }
  
  /**
   * @controller-api
   */
  public function create(\stdClass $table, $filename = NULL) {
    // module bootstrappen?
    if (empty($filename)) {
      $filename = 'export';
    }
    
    $excel = new PHPExcel();
    $sheet = $excel->getSheet(0);
    
    // header
    $c = ord('A');
    foreach ($table->columns as $column) {
      $sheet->setCellValue(chr($c).'1', $column->label);
      $sheet->getColumnDimension(chr($c))->setAutoSize(TRUE);
      $c++;
    }
    
    $data = array();
    
    // data (wir müssen alle in strings bzw values für excel umarbeiten)
    foreach ($table->data as $rKey => $row) {
      foreach ($row as $key => $value) {
        if (array_key_exists($key, $table->columns)) {
          $column = $table->columns[$key];
        
          if ($column->type === 'Array' || $column->type === 'array') {
            $value = json_encode($value);
          }
          
          if (is_array($value) || is_object($value)) {
            $value = json_encode($value); // gibt so oder so einen fehler, so ist er wenigstnes silent und besser als zu string zu casten
          }
        
          $data[$rKey][$key] = $value;
        }
      }
    }
    
    $sheet->fromArray($data, NULL, 'A2');
    
    $excel->getProperties()->setCustomProperty('filename', $filename);
    
    return new ServiceResponse(Service::OK, $excel, ServiceResponse::XLSX);
  }
  
  public function write(\stdClass $table, File $file) {
    $response = $this->create($table);
    $writer = \PHPExcel_IOFactory::createWriter($response->getBody(), 'Excel2007');
    $writer->save((string) $file);
  }
  
  /**
   * Konvertiert ein Excel in einen Array
   *
   * Erwartet excelFile als Uploaded File
   * der zurückgegebene Array hat jedoch keine Zahlen Columns sondern auch einen 0 basierten index
   * auch die Rows sind 0 basierend
   *  
   * @controller-api
   * @return array
   */
  public function convert(\stdClass $table, $excelFile = NULL, $removeEmpty = TRUE) {
    $excelFile = $excelFile ?: $this->v->validateUploadedFile('excelFile');
    
    $reader = PHPExcel_IOFactory::createReaderForFile((string) $excelFile);
    $excel = $reader->load((string) $excelFile);
    $sheet = $excel->getSheet(0);
    
    $columns = isset($table->columns) ? (array) $table->columns : array();
    
    $nullValue = ''; // sadly json macht aus NULL den string "null" in js
    $data = array();
    $mCell = 0;
    foreach ($sheet->getRowIterator() as $wsRow) {
      $row = array();
      $empty = TRUE;
      foreach ($wsRow->getCellIterator() as $cell) {
        // $cell->getDataType() gibts sogar auch

        $cellIndex = h::getColumnIndex($cell->getColumn());
        $mCell = max($mCell, $cellIndex+1);
      
        $row[$cellIndex] = $value = $this->convertValue(
                            $cell->getValue(),
                            isset($columns[$cellIndex]) && isset($columns[$cellIndex]->type) ? $columns[$cellIndex]->type : NULL
                           );
        if ($value != '') {
          $empty = FALSE;
        }
      }
      
      if (!$removeEmpty || !$empty) {
        ksort($row);
        $data[] = array_replace(array_fill(0, $mCell, $nullValue), $row);
      }
    }

    return new ServiceResponse(Service::OK, $data, ServiceResponse::JSON_UPLOAD_RESPONSE);
  }
  
  protected function convertValue($value, $typeString = NULL) {
    if (is_string($value))
      $value = trim($value);
      
    switch ($typeString) {
      case 'string':
        return (string) $value; // convert Richttext runs to text here
      
      case NULL:
        return $value;
      
      case 'Array':
      case 'array':
        // convenience: eckige klammern vergessen
        if (mb_strpos($value,'[') !== 0) {
          $value = '['.$value.']';
        }
        return json_decode($value);
    }
    
    return $value;
  }
  
  public function setValidationPackage(ValidationPackage $v) {
    $this->v = $v;
    return $this;
  }
}
?>