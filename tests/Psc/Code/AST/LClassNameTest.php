<?php

namespace Psc\Code\AST;

/**
 * @group class:Psc\Code\AST\LClassName
 */
class LClassNameTest extends \Psc\Code\Test\Base {
  
  protected $lClassName;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\AST\LClassName';
    parent::setUp();
    $this->lClassName = new LClassName('Psc\Code\AST\Something');
  }
  
  public function testToString() {
    $this->assertEquals('Psc\Code\AST\Something', $this->lClassName->toString());
  }
}
?>