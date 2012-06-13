<?php

namespace Psc\UI;

use Psc\Data\Type\Type;

/**
 * @group class:Psc\UI\DataScreener
 */
class DataScreenerTest extends \Psc\Code\Test\Base {
  
  protected $dataScreener;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\DataScreener';
    parent::setUp();
    $this->dataScreener = new DataScreener();
  }
  
  /**
   * @dataProvider provideToString
   */
  public function testToStringAcceptance($expectedValue, $value, $type) {
    $this->assertEquals($expectedValue, $this->dataScreener->toString($value, $type));
  }
  
  public static function provideToString() {
    $tests = array();
    $test = function ($expectedValue, $value, $type) use (&$tests) {
      $tests[] = array($expectedValue, $value, Type::create($type));
    };
    
    $test('21.11.1984 00:02', new \Psc\DateTime\DateTime('21.11.1984 00:02:02'), 'DateTime');
    $test('21.11.1984', new \Psc\DateTime\Date('21.11.1984'), 'Date');
    $test('eins, zwei, drei', new \Psc\Data\ArrayCollection(array('eins','zwei','drei')), 'Collection');
    
    return $tests;
  }
}
?>