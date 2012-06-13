<?php

namespace Psc\JS;

use Psc\Code\Code;
use Psc\Code\AST;

require_once __DIR__.DIRECTORY_SEPARATOR.'ParserBaseTest.php';

/**
 * @group class:Psc\JS\Parser
 * @experiment
 */
class ParserTest extends ParserBaseTest {
  
  protected $parser;
  
  public function setUp() {
    $this->chainClass = 'Psc\JS\Parser';
    parent::setUp();
    $this->parser = new Parser();
    
    $this->dsl = new AST\DSL();
  }

  public function testBasicFunctionParsing() {
    extract($this->dsl());
    
    $source  = 'function GameLogic(sounds, options, identifier) {';
    $source .= '}';
    
    $expectedFunction = new AST\LFunction(
      'GameLogic',
      new AST\LParameters(array(
        $parameter('sounds'),
        $parameter('options'),
        $parameter('identifier')
      )),
      new AST\LStatements(array())
    );
    
    $this->assertEquals(array($expectedFunction), $this->parse($source));
  }
  
  public function testBasicFunctionWithoutParamsParsing() {
    extract($this->dsl());
    
    $source  = 'function GameLogic() {';
    $source .= '}';
    
    $expectedFunction = new AST\LFunction(
      'GameLogic',
      new AST\LParameters(array()),
      new AST\LStatements(array())
    );
    
    $this->assertEquals(array($expectedFunction), $this->parse($source));
  }

  
  
  protected function assertInstanceOfL($element, $actual, $msg = '') {
    $element = Code::expandNamespace('L'.$element, 'Psc\Code\AST');
    
    return $this->assertInstanceOf($element, $actual, $msg);
  }
  
  /**
   * @return Array Closures
   */
  protected function dsl() {
    return $this->dsl->getClosures();
  }
  
  protected function parse($source) {
    $ast = $this->parser->parse($source);
    
    return $ast;
  }
  
  public function testAcceptance() {
    $source = "";
    $source .= "var timer = new Timer(10000);\n";
    $source .= "var rounds = 5;\n";
    
    $source .= 'if (round == 0) {';
    $source .= "  playSounds('2-TAF-003','2-TAF-004');\n";
    $source .= '}'."\n";
    
    // ===
    
/*
     Darstellung im CMS:
     
     Text mit Text-Links die ein Popup Öffnen (slideup vll)
     
     evaluate(
      ''
     )
     
     
    $parsed = new Game(
      array (
        new Property('timer', new Timer(10000)),
        new Property('rounds', 5)
      ),
      
      array (
        new Condition(new Equals(new Variable('round'), 0), 
          new PlaySounds(
            new Reference('startSounds')
          )
        )
      )
    );
*/
  }
}
?>