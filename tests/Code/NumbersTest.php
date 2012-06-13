<?php

namespace Psc\Code;

/**
 * @group class:Psc\Code\Numbers
 */
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
  
  /**
   * @dataProvider getTestRomans()
   */
  public function testGetRoman($roman, $integer) {
    $this->assertEquals($roman, Numbers::toRoman($integer));
  }
  
  
  public static function getTestRomans() {
    $tests = array();
    
    $tests[] = array('IV', 4);
    $tests[] = array('XV', 15);
    $tests[] = array('L', 50);
    $tests[] = array('DIV', 504);
    $tests[] = array('CL', 150);
    $tests[] = array('MCL', 1150);
    $tests[] = array('CMXCIX', 999);
    $tests[] = array('MCMLXXXIV', 1984); // dies zeigt, dass der algorithmus mit substraktionsregel arbeitet http://de.wikipedia.org/wiki/R%C3%B6mische_Zahlendarstellung#Subtraktionsregel
    $tests[] = array('MMMMM', 5000); // dies zeigt dass dies nicht diese komische zeichen benutzt
    
    
    return $tests;
  }
}
?>