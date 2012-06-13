<?php

namespace Psc\Code\AST;

/**
 * @group class:Psc\Code\AST\LValue
 */
class LValueTest extends \Psc\Code\Test\Base {
  
  protected $lValue;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\AST\LValue';
    parent::setUp();
    $this->lValue = new LValue(array());
  }
  
  public function testAcceptance() {
    $this->assertEquals(array(), $this->lValue->unwrap());
  }
}
?>