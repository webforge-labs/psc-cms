<?php

namespace Psc\TPL;

use \Psc\TPL\TPL;

/**
 * @group class:Psc\TPL\TPL
 */
class TPLTest extends \Psc\Code\Test\Base {
  
  protected $c = '\Psc\TPL\TPL';
  
  public function testMiniTemplate() {
    
    $tpl = "OIDs %usedOIDs% are used in this game";
    $repl = "OIDs 11601,11602,11603,11604,11605,11606,11617,11618,11619,11620,11621,11653,11624,11625,11626,11627,11633,11639,11640,11647,11648,11650,11654,11655,11656,11657,11658,11659,11660 are used in this game";
    
    $vars = array('usedOIDs'=>
                  '11601,11602,11603,11604,11605,11606,11617,11618,11619,11620,11621,11653,11624,11625,11626,11627,11633,11639,11640,11647,11648,11650,11654,11655,11656,11657,11658,11659,11660'
                  );
    
    $this->assertEquals($repl, TPL::miniTemplate($tpl, $vars));
  }

  public static function provideBUIMarkupText() {
    $tests = array();
  
    $test = function($text, $expected) use (&$tests) {
      $tests[] = array($expected, $text);
    };

    $test('//italic//', '<em>italic</em>');
    $test('something //italic//', 'something <em>italic</em>');
    $test('something **bold**', 'something <strong>bold</strong>');

    $test('//**bold and italic**//', '<em><strong>bold and italic</strong></em>');
    $test('**//bold and italic//**', '<strong><em>bold and italic</em></strong>');
  
    return $tests;
  }

  /**
   * @dataProvider provideBUIMarkupText
   */
  public function testBUIMarkup($expectedHTML, $text) {
    $this->assertEquals($expectedHTML, TPL::replaceBUIMarkup($text));
  }
}
