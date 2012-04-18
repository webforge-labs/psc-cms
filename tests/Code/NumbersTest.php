<?php

namespace Psc\Code;

class NumbersTest extends \Psc\Code\Test\Base {
  
  protected $numbers;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Numbers';
    parent::setUp();
  }
  
  /**
   * @dataProvider getTestFloats
   */
  public function testParseFloat($float, $string, $thousands = Numbers::USE_LOCALE, $point = Numbers::USE_LOCALE) {
    $this->assertEquals($float, Numbers::parseFloat($string, $thousands, $point));
  }
    
  public static function getTestFloats() {
    $tests = array();
      
    $thousands = '.';
    $decimalPoint = ',';
    $tests[] = array(5414.50, '5.414,50', $thousands, $decimalPoint);
    $tests[] = array(0.5, '000,50', $thousands, $decimalPoint);
    $tests[] = array(-0.32, '-0,32', $thousands, $decimalPoint);
    $tests[] = array(-1000000.32, '-1.000.000,32', $thousands, $decimalPoint);
 
    return $tests;
  }
}
?>