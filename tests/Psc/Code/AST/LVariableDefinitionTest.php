<?php

namespace Psc\Code\AST;

/**
 * @group class:Psc\Code\AST\LVariableDefinition
 */
class LVariableDefinitionTest extends \Psc\Code\Test\Base {
  
  protected $def;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\AST\LVariableDefinition';
    parent::setUp();
    // set $identifier to 17
    $this->valueDef = new LVariableDefinition(new LVariable('identifier', new LType('Integer')),
                                              new LBaseValue(17)
                                             );
    $this->def = new LVariableDefinition(new LVariable('identifier', new LType('Integer')), NULL);
  }
  
  public function testHasValue() {
    $this->assertTrue($this->valueDef->hasValue());
    $this->assertFalse($this->def->hasValue());
  }
}
?>