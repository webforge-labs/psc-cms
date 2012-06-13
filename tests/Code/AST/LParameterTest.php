<?php

namespace Psc\Code\AST;

/**
 * @group class:Psc\Code\AST\LParameter
 */
class LParameterTest extends \Psc\Code\Test\Base {
  
  protected $lParameter;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\AST\LParameter';
    parent::setUp();
    $this->lParameter = new LParameter('parameter1', new LType('String'));
  }
  
  public function testAcceptance() {
    $this->assertEquals('parameter1',$this->lParameter->getName());
  }
}
?>