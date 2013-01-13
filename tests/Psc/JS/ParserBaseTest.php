<?php

namespace Psc\JS;

use Psc\Code\Code;
use Psc\Code\AST;

/**
 * @group class:Psc\JS\Parser
 * @experiment
 */
abstract class ParserBaseTest extends \Psc\Code\Test\Base {
  
  protected $parser;
  
  public function setUp() {
    $this->chainClass = 'Psc\JS\Parser';
    parent::setUp();
    $this->parser = new Parser();
    
    $this->dsl = new AST\DSL();
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
}
?>