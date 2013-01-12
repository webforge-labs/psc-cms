<?php

namespace Psc\Code\Generate;

/**
 * @group class:Psc\Code\Generate\Expression
 */
class ExpressionTest extends \Psc\Code\Test\Base {
  
  protected $expression;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Generate\Expression';
    parent::setUp();
    $this->expression = new Expression($this->code = 'new \Psc\Data\Type\Type::create(\'String\')');
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Psc\Data\Exportable', $this->expression);
    $this->assertEquals($this->code, $this->expression->export());
    
    $this->assertInstanceOf('Psc\Code\PHPInterface', $this->expression);
    $this->assertEquals($this->code, $this->expression->php());
  }
}
?>