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
    $this->dsl = new ExampleDSL();
  }
  
  public function testGetClosures() {
    $closures = $this->dsl->getClosures();
    
    $this->assertArrayNotHasKey('getClosures', $closures, 'critical: Closures darf auf gar keinen fall getClosures exportieren');
    $this->assertArrayNotHasKey('exampleVariableDefinition', $closures);
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
    $this->assertEquals(array(), $var->getInitializer()->unwrap());
  }
  
  public function testValueTypeInferring() {
    $this->assertInstanceOf('Psc\Code\AST\LValue', $value = $this->dsl->value((object) array('test'=>'v1')));
    $this->assertInstanceOf('Webforge\Types\ObjectType', $value->getInferredType()->unwrap());
  }
  
  public function testConstructCallNoParams() {
    $this->assertInstanceOf('Psc\Code\AST\LConstructExpression', $construct = $this->dsl->construct('MyNiceObjectClass'));
  }

  public function testConstructWithArguments() {
    $this->assertInstanceOf('Psc\Code\AST\LConstructExpression', $construct = $this->dsl->construct('MyNiceObjectClass',
                                                                                                    $this->dsl->exampleArguments()
                                                                                                   )
                            );
    $this->assertInstanceOf('Psc\Code\AST\LArguments', $arguments = $construct->getArguments());
  }
  
  public function testArguments() {
    $this->assertInstanceOf('Psc\Code\AST\LArguments',
                            $args = $this->dsl->exampleArguments()
                           );
    
    $this->assertInstanceOf('Psc\Code\AST\LArgument', $arg1 = $args->getArgument(0));
    $this->assertInstanceOf('Psc\Code\AST\LArgument', $arg2 = $args->getArgument(1));
    
    return $args;
  }
  
  public function testArgument() {
    $this->assertInstanceOf('Psc\Code\AST\LArgument', $arg = $this->dsl->argument($this->dsl->value('i am value 1')));
    $this->assertInstanceOf('Psc\Code\AST\LValue', $arg->getBinding());
  }
  
  public function testArgumentObjectValue() {
    $this->assertInstanceOf('Psc\Code\AST\LArgument', $arg = $this->dsl->argument($o = (object) array('something'=>'defined')));
    $this->assertInstanceOf('Psc\Code\AST\LValue', $arg->getBinding());
    $this->assertEquals($o, $arg->getBinding()->unwrap());
  }
}
?>