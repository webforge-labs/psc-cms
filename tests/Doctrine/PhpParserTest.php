<?php

namespace Psc\Doctrine;

/**
 * @group class:Psc\Doctrine\PhpParser
 */
class PhpParserTest extends \Psc\Code\Test\Base {
  
  protected $phpParser;
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\PhpParser';
    parent::setUp();
    $this->phpParser = new PhpParser();
  }
  
  public function testAcceptance() {
    $this->assertEquals(__CLASS__, $this->phpParser->findFirstClass(__FILE__));
  }
}
?>