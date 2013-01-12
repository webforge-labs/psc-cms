<?php

namespace Psc\Code\AST;

/**
 * @group class:Psc\Code\AST\LStatements
 */
class LStatementsTest extends \Psc\Code\Test\Base {
  
  protected $lStatements;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\AST\LStatements';
    parent::setUp();
    $this->lStatements = new LStatements();
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Psc\Code\AST\ElementCollection', $this->lStatements);
  }
}
?>