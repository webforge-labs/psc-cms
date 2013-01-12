<?php

namespace Psc\JS;

use Psc\Code\Code;
use Psc\Code\AST;

require_once __DIR__.DIRECTORY_SEPARATOR.'ParserBaseTest.php';

/**
 * @group class:Psc\JS\Parser
 * @experiment
 */
class ParserValuesTest extends ParserBaseTest {
  
  protected $parser;
  
  public function setUp() {
    $this->chainClass = 'Psc\JS\Parser';
    parent::setUp();
    $this->parser = new Parser();
    
    $this->dsl = new AST\DSL();
  }

  /**
   * @dataProvider provideBaseValues
   */
  public function testBaseValueParsing($sourceValue, $expectedValue) {
    $source = 'var testVar = '.$sourceValue.';';
    $ast = $this->parser->parse($source);
    
    $variableDefinitions = $ast[0];
    $variableDefinition = $variableDefinitions[0];
    $this->assertInstanceOfL('VariableDefinition', $variableDefinition);
    
    $this->assertEquals($expectedValue, $variableDefinition->getValue()->unwrap());
  }

  public static function provideBaseValues() {
    $tests = array();
    $v = function ($jsValue, $phpValue) use (&$tests) {
      $tests[] = array($jsValue, $phpValue);
    };
    
    $v('true', TRUE);
    $v('false', FALSE);
    $v('7', 7);
    $v("'someString'", 'someString');
    $v('"someString"', 'someString');
    $v('[]', array());
    $v('{}', new \stdClass());
    
    return $tests;
  }
}
?>