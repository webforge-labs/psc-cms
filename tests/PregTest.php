<?php

namespace Psc;

/**
 * @group class:Psc\Preg
 */
class PregTest extends \PHPUnit_Framework_TestCase {
  
  
  public function testMatchArray() {
    
    $matchers = array('/web-dl/i' => 'one',
                      '/^mother/' => 'two');
    
    $this->assertEquals('one',Preg::matchArray($matchers,'How.I.Met.Your.Mother.S06E13.Schlechte.Nachrichten.German.Dubbed.WEB-DL.XViD'));
    $this->assertNotEquals('two',Preg::matchArray($matchers,'mother.web-dl')); // der erste gewinnt
    
    try {
      Preg::matchArray($matchers,'nix');
      
      $this->fail('Muss eine Exception: \Psc\NoMatchException schmeissen');
    } catch (\Psc\NoMatchException $e) {
    }
    

    $value = 'How.I.Met.Your.Mother.S06E13.Schlechte.Nachrichten.German.Dubbed.WEB-DL.XViD';
    $this->assertEquals('WEB-DL',Preg::matchArray(Array(
            '/dvdrip/i' => 'DVDRip',
            '/WEB-DL/i' => 'WEB-DL'
    ),$value));
    
  }
  
  /**
   * @dataProvider provideTestqmatch
   */
  public function testqmatch($string, $rx, $set, $expectedReturn) {
    $this->assertEquals($expectedReturn, Preg::qmatch($string, $rx, $set));
  }
  
  public static function provideTestqmatch() {
    $tests = array();
    $equals = function ($expectedReturn, $string, $rx, $set) use (&$tests) {
      $tests[] = array($string, $rx, $set, $expectedReturn);
    };
    
    $s1 = 'How.I.Met.Your.Mother.S06E13.Schlechte.Nachrichten.German.Dubbed.WEB-DL.XViD';
    $episodeRX = '/How.I.Met.Your.Mother.S([0-9]{2})E([0-9]{2,})/'; // jaja ich weiß, . nicht escaped
    //var_dump(Preg::match($s1, $episodeRX,$match),$match); // deppen ausscließen
    
    // set 0
    $equals($s1,
            $s1, '/^.*$/', 0);

    // set 1
    $equals('06',
            $s1, $episodeRX, 1); 

    // set 2
    $equals('13',
            $s1, $episodeRX, 2);
    
    // set größer match ergibt notice, das können wir schlecht testen (ist aber gewollt)
//    $equals('13',
//            $s1, $episodeRX, 3);
    
    // set-array
    $equals(array('06','13'),
            $s1, $episodeRX, array(1,2));

    // set-array
    $equals(array('13'),
            $s1, $episodeRX, array(2));

    // set-array empty
    $equals(array(),
            $s1, $episodeRX, array());
    
    // no match
    $equals(NULL,
            $s1,'/(wolf|katze)/i', 1);
    $equals(NULL,
            $s1,'/(wolf|katze)/i', array(0,1));
    
    // set-array oversized (schmeisst keine notice)
    $equals(array('06','13'),
            $s1, $episodeRX, array(1,2,3));

    $equals(array('06','13'),
            $s1, $episodeRX, array(1,2,3,4,5,6,7,13));
    
    return $tests;
  }
}

?>