<?php

namespace Psc\PHPExcel;

interface Transporter {
  
  /**
   * @param char $excelCellColumn nur der Buchstabe der Spalte (A ist die erste)
   * @param Array $key die keys die aus dem  Datainput gelesen werden vor jeden $key wird array('rows',$rowIndex) eingefügt
   * @param string $label das Label für den Header, wenn NULL gibt es keinen Header
   */
  public function addColumnMapping($excelCellColumn, Array $key, $label = NULL, $size = NULL);

  
}