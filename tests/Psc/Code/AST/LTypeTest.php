<?php

namespace Psc\Code\AST;

/**
 * @group class:Psc\Code\AST\LType
 */
class LTypeTest extends \Psc\Code\Test\Base {
  
  protected $lType;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\AST\LType';
    parent::setUp();
    $this->lType = new LType('Integer');
  }
  
  public function testInnerPscType() {
    $this->assertInstanceOf('Psc\Data\Type\IntegerType', $this->lType->unwrap());
    $this->assertEquals('Integer', $this->lType->getName());
  }
}
?>