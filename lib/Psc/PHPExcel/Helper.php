<?php

namespace Psc\PHPExcel;

class Helper {
  
  /**
   * 
   * @return string ohne erlaubte sonderzeichen und maximal 31 zeichen lang
   */
  public static function sanitizeSheetTitle($title) {
    
    $title = str_replace(array('*', ':', '/', '\\', '?', '[', ']'), '-', $title);
    $title = mb_substr($title, 0, 31);
    
    return $title;
  }
  
  public static function getColumnIndex($columnString) {
    // es ist nicht so trivial wie ord() umzurechnen!
    return \PHPExcel_Cell::columnIndexFromString($columnString)-1; // base-1 ist natürlich quatsch hier weil das nicht bidirektional zu getColumnString ist, deshalb wrappen wir hier den excel kram
  }
  
  public static function getColumnString($columnIndex) {
    return \PHPExcel_Cell::stringFromColumnIndex($columnIndex);
  }
}