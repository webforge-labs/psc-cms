<?php

namespace Psc\Code\AST;

/**
 * @group class:Psc\Code\AST\DSL
 */
class DSLTest extends \Psc\Code\Test\Base {
  
  protected $dSL;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\AST\DSL';
    parent::setUp();
    $this->dsl = new DSL();
  }
  
  public function testGetClosures() {
    $closures = $this->dsl->getClosures();
    
    $this->assertArrayNotHasKey('getClosures', $closures, 'critical: Closures darf auf gar keinen fall getClosures exportieren');
    $this->assertArrayHasKey('parameter',$closures);
    $this->assertArrayHasKey('type',$closures);
  }
  
  public function testInParametersCannotAInnerParameterBeWrongException() {
    $this->setExpectedException('InvalidArgumentException');
    $this->dsl->parameters('thisisokay', 7, 'but the 7 not');
  }
  
  public function testFunction() {
    $this->assertInstanceOf('Psc\Code\AST\LFunction',
                            $function = $this->dsl->function_(
                              'doSomething',
                              $this->dsl->parameters(
                                $this->dsl->parameter('param1'),
                                $this->dsl->parameter('param2')
                              )
                            )
                           );
  }
  
  
  public function testParameterWithoutTypeDefaultsToMixed() {
    $this->assertInstanceOf('Psc\Code\AST\LParameter', $sounds = $this->dsl->parameter('sounds'));
    $this->assertEquals('Mixed', $sounds->getType()->getName());
  }

  public function testParameterWithType() {
    $this->assertInstanceOf('Psc\Code\AST\LParameter', $sounds = $this->dsl->parameter('sounds', $this->dsl->type('Array')));
    $this->assertInstanceOf('Psc\Code\AST\LType', $sounds->getType());
    $this->assertEquals('Array', $sounds->getType()->getName());
  }
  
  public function testVarDef() {
    $this->assertInstanceOf('Psc\Code\AST\LVariableDefinition', $var = $this->dsl->var_('sounds', 'Array', array()));
    $this->assertEquals('sounds', $var->getVariable()->getName());
    $this->assertEquals('Array', $var->getVariable()->getType()->getName());
    $this->assertEquals(array(), $var->getValue()->unwrap());
  }
}
?>