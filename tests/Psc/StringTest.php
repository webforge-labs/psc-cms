<?php

namespace Psc;

use \Webforge\Common\String AS S;

/**
 * @group class:Webforge\Common\String
 */
class StringTest extends \Psc\Code\Test\Base {

  public function testIndent() {
    
    $string = 'aaa';
    $expect = '  aaa';
    $this->assertEquals($expect,S::indent($string,2));
    
    $string  = "aaa\n";
    $string .= "bbb\n";
    $string .= "cccc\n";
    
    $expect  = "  aaa\n";
    $expect .= "  bbb\n";
    $expect .= "  cccc\n";
    $this->assertEquals($expect,S::indent($string,2));

    $string  = "aaa\n";
    $string .= "bbb\n";
    $string .= "cccc";

    $expect  = "  aaa\n";
    $expect .= "  bbb\n";
    $expect .= "  cccc";
    $this->assertEquals($expect,S::indent($string,2));
    
    $string  = "aaa\n";
    $string .= "bbb\n";
    $string .= "cccc\n";
    $this->assertEquals($string,S::indent($string,0));
  }
  
  public function testPrefix() {
    $string = 'aaa';
    $expect = '[prefix]aaa';
    $this->assertEquals($expect,S::prefixLines($string,'[prefix]'));
    
    $string  = "aaa\n";
    $string .= "bbb\n";
    $string .= "cccc\n";
    
    $expect  = "[prefix]aaa\n";
    $expect .= "[prefix]bbb\n";
    $expect .= "[prefix]cccc\n";
    $this->assertEquals($expect,S::prefixLines($string,'[prefix]'));

    $string  = "\r\naaa\r\n";
    $string .= "bbb\r\n";
    $string .= "cccc\r\n";
    
    $expect  = "[prefix]\r\n[prefix]aaa\r\n";
    $expect .= "[prefix]bbb\r\n";
    $expect .= "[prefix]cccc\r\n";
    $this->assertEquals($expect,S::prefixLines($string,'[prefix]'));
  }
  
  public function testLineNumbers() {
    $string = <<<'JAVASCRIPT'
jQuery.when( jQuery.psc.loaded() ).then( function(main) {
  main.getLoader().onReady(['Psc.UI.Main', 'Psc.UI.Tabs'], function () {
    var j = new Psc.UI.Main({
      'tabs': new Psc.UI.Tabs({
        'widget': $('#psc-ui-tabs')
      })
    });
  });
 });
});
JAVASCRIPT;

    $expect = <<<'JAVASCRIPT'
1  jQuery.when( jQuery.psc.loaded() ).then( function(main) {
2    main.getLoader().onReady(['Psc.UI.Main', 'Psc.UI.Tabs'], function () {
3      var j = new Psc.UI.Main({
4        'tabs': new Psc.UI.Tabs({
5          'widget': $('#psc-ui-tabs')
6        })
7      });
8    });
9   });
10 });
JAVASCRIPT;

    $this->assertEquals($expect, S::lineNumbers($string));
  }
  
  public function testExpandEnd() {
    $this->assertEquals('StringType', S::expand('String', 'Type', S::END));
    $this->assertEquals('StringType', S::expand('StringType', 'Type', S::END));
  }

  public function testExpandStart() {
    $this->assertEquals('@return', S::expand('return', '@', S::START));
    $this->assertEquals('@return', S::expand('@return', '@', S::START));
  }
  
  public function testCutAtLast() {
    $sentence = "this is a sentence";
    //           0123456789a1234567
    $ender = '...';
    
    // no cutting-test
    $this->assertEquals($sentence, S::cutAtLast($sentence, 18, ' ', $ender));
    
    // cutting at whitespace not inbetween "sentence"
    $this->assertEquals('this is a'.$ender, S::cutAtLast($sentence, 12, ' ', $ender));

    // cutting at whitespace not inbetween "sentence"
    $this->assertEquals('this is a'.$ender, S::cutAtLast($sentence, 17, ' ', $ender));

    // cutting at whitespace not inbetween "is"
    $this->assertEquals('this'.$ender, S::cutAtLast($sentence, 5, ' ', $ender));
    
    // non defined
    $this->assertEquals(''.$ender, S::cutAtLast($sentence, 17, 'b', $ender));
  }
  
  /**
   * @dataProvider getSymmetricWrapTests
   */
  public function testSymmetricWrap($input, $symmetric, $expected) {
    $this->assertEquals($expected, S::swrap($input, $symmetric));
  }
  
  public static function getSymmetricWrapTests() {
    $tests = array();
    
    $tests[] = array(
      '2-TAF_0001',
      '(',
      '(2-TAF_0001)'
    );

    $tests[] = array(
      '2-TAF_0001',
      '[',
      '[2-TAF_0001]'
    );
    
    $tests[] = array(
      '2-TAF_0001',
      '[',
      '[2-TAF_0001]'
    );

    $tests[] = array(
      '2-TAF_0001',
      ']',
      ']2-TAF_0001]'
    );

    $tests[] = array(
      "what's up",
      '"',
      '"what\'s up"'
    );
    
    return $tests;
  }
}
?>