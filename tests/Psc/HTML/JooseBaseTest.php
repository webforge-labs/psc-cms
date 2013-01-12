<?php

namespace Psc\HTML;

/**
 * @group class:Psc\HTML\JooseBase
 */
class JooseBaseTest extends \Psc\Code\Test\Base {
  
  protected $jooseBase;
  
  public function setUp() {
    $this->chainClass = 'Psc\HTML\JooseBase';
    parent::setUp();
    $this->jooseBase = $this->getMockForAbstractClass($this->chainClass, array('Psc.UI.TestObject'));
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Psc\HTML\HTMLInterface',$this->jooseBase);
    
    // @TODO javascript-writer bauen und dann hier das autoload und constructCode testen
  }
}
?>