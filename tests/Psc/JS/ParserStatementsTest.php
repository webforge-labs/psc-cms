<?php

namespace Psc\JS;

use Psc\Code\Code;
use Psc\Code\AST;
use stdClass;

require_once __DIR__.DIRECTORY_SEPARATOR.'ParserBaseTest.php';

/**
 * @group class:Psc\JS\Parser
 */
class ParserStatementsTest extends ParserBaseTest {
  
  protected $parser;
  
  public function setUp() {
    $this->chainClass = 'Psc\JS\Parser';
    parent::setUp();
    $this->parser = new Parser();
    
    $this->dsl = new AST\DSL();
  }
  
  /**
   *
   * <J_STATEMENT>  
   * : <J_BLOCK>
   * | <J_VAR_STATEMENT> 
   * | <J_EMPTY_STATEMENT>
   * | <J_EXPR_STATEMENT> 
   * | <J_IF_STATEMENT>  
   * | <J_ITER_STATEMENT>
   * | <J_CONT_STATEMENT>
   * | <J_BREAK_STATEMENT> 
   * | <J_RETURN_STATEMENT> 
   * | <J_WITH_STATEMENT>
   * | <J_LABELLED_STATEMENT> 
   * | <J_SWITCH_STATEMENT>
   * | <J_THROW_STATEMENT> 
   * | <J_TRY_STATEMENT>
   */
  
  /**
   <J_BLOCK> 
	: "{" "}" 
	| "{" <J_STATEMENT_LIST> "}"
	;
  */
  public function testBlock() {
  }
  
  public function testVariableDefinitions() {
    extract($this->dsl());
    
    $source = 'var sounds = [], options = {}, identifier = 7;';
    $statements = $this->parse($source);
    
    // och manno. Wir können leider nicht ein gesamt "assertEquals" machen, weil so variablen wie
    // inferredType oder andere State-Variablen dann leider nicht equal sind
    // leider lässt sich assertEquals von PHPUnit auch nicht hacken, sodass wird diese Properties rauslassen
    $this->assertEqualsVariableDefinition($statements[0][0], $var('sounds', 'Array', array()));
    $this->assertEqualsVariableDefinition($statements[0][1], $var('options', 'Object', new stdClass));
    $this->assertEqualsVariableDefinition($statements[0][2], $var('identifier', 'Integer', 7));
  }
  
  protected function assertEqualsVariableDefinition(AST\LVariableDefinition $expected, AST\LVariableDefinition $actual) {
    $this->assertEquals($expected->getVariable()->getName(),
                          $actual->getVariable()->getName()
                        );
    $this->assertEquals($expected->getVariable()->getType()->unwrap(),
                          $actual->getVariable()->getType()->unwrap(),
                          'type von '.$actual->getVariable()->getName()
                       );
    $this->assertEquals($expected->getInitializer()->unwrap(),
                          $actual->getInitializer()->unwrap(),
                          'initializer von '.$actual->getVariable()->getName()
                       );
  }

  /**
   * @expectedException Psc\JS\NotAllowedParseError
   */
  public function testVariableDefinitionException() {
    extract($this->dsl());
    
    $source = 'var sounds = [], options = {}, identifier;';
    
    $this->parse($source);
  }
}
?>