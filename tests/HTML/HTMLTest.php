<?php

namespace Psc\HTML;

/**
 * @group class:Psc\HTML\HTML
 */
class HTMLTest extends \Psc\Code\Test\Base {
  
  protected $hTML;
  
  public function setUp() {
    $this->chainClass = 'Psc\HTML\HTML';
    parent::setUp();
    //$this->hTML = new HTML();
  }
  
  /**
   * @dataProvider provideString2class
   */
  public function teststring2Class($string, $class) {
    $this->assertEquals($class, HTML::string2class($string));
  }
  
  public static function provideString2Class() {
    $tests = array();
    $test = function ($string, $class) use (&$tests) {
      $tests[] = array($string, $class);
    };
    
    $test('Meta','meta');
    $test('MetaTest','meta-test');
    $test('MetaTestLongEnough','meta-test-long-enough');
    $test('This\Is\A\Class', 'this-is-a-class');
    $test('fiddle[things]', 'fiddle-things-');
    $test('The9ThTest', 'the-9-th-test');
    $test('The9thTest', 'the-9th-test');
    $test('', '');
    $test('schnurps@ps.de', 'schnurps-ps-de');
    $test(' ', '');
    
    return $tests;
  }
}
?>