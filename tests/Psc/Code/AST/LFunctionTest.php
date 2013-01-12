<?php

namespace Psc\Code\AST;

/**
 * @group class:Psc\Code\AST\LFunction
 */
class LFunctionTest extends \Psc\Code\Test\Base {
  
  protected $lFunction;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\AST\LFunction';
    parent::setUp();
    $this->lFunction = new LFunction(
        'gameLogic',
        new LParameters(
          array(
            new LParameter('sounds', new LType('Array')),
            new LParameter('options', new LType('Object')),
            new LParameter('identifier', new LType('Integer'))
          )
        )
    );
  }
  
  public function testGetParameters() {
    $this->assertInstanceOf('Psc\Code\AST\LParameters', $this->lFunction->getParameters());
    $this->assertTrue($this->lFunction->getParameters()->hasParameter('sounds'));
  }
}
?>