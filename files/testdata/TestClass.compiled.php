<?php

namespace Psc;

use Psc\Code\Generate\SomeClassForAHint,
    stdClass,
    Special\classn\In\nspace\Banane;

abstract class TestClass extends Object {
  
  protected $prop1 = 'banane';
  
  public static $prop2;
  
  public function comboBox($label, $name, $selected = NULL, $itemType = NULL, Array $commonItemData = array()) {
    // does not matter
    
    $oderDoch = true;
  }
  
  public static function factory(SomeClassForAHint $dunno) {
  }
  
  abstract public function banane();
  
  public function method2($num, Array $p1, stdClass $std = NULL, $bun = array()) {
    $bimbam = 'pling';
    
    return 'schnurpsel';
  }
}
?>