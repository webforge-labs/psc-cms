<?php

namespace Psc\FE;

class HelperTest extends \Psc\Code\Test\Base {
  
  protected $helper;
  
  public function setUp() {
    $this->chainClass = 'Psc\FE\Helper';
    parent::setUp();
  }
  
  /**
   * @dataProvider listStringsParams
   */
  public function testListStrings(Array $array, $expectedString, $andsep, $sep, $print) {
    $this->assertEquals(
      $expectedString,
      \Psc\FE\Helper::listStrings($array, $sep, $andsep, $print)
    );
  }
  
  public static function listStringsParams() {
    $tests = array();
    
    $test = function(Array $array, $expectedString, $andsep = 'und', $print = NULL) use (&$tests) {
      $tests[] = array($array, $expectedString, $andsep, ', ', $print);
    };
    
    // with andsep
    $test(array('item1','item2','item3'),
          'item1, item2 und item3',
          ' und '
         );
    $test(array('item1','item2'),
          'item1 und item2',
          ' und '
         );
    $test(array('item1'),
          'item1',
          'und'
         );
    
    // without andsep
    $test(array('item1','item2','item3'),
          'item1, item2, item3',
          NULL
         );
    $test(array('item1'),
          'item1',
          NULL
         );
    
    // empty
    $test(array(), '');


    // with closure
    $test(array('item1','item2','item3'),
          '[item1], [item2] ooouu [item3]',
          ' ooouu ',
          function ($item) { return sprintf('[%s]',$item); }
         );
    
    return $tests;
  }
}
?>