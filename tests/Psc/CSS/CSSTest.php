<?php

namespace Psc\CSS;

/**
 * @group class:Psc\CSS\CSS
 */
class CSSTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $cSS;
  
  public function setUp() {
    $this->chainClass = 'Psc\CSS\CSS';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $manager = CSS::getManager();
    $this->assertInstanceOf('Psc\CSS\Manager', $manager);    
  }
}
