<?php

namespace Psc\HTML;

/**
 * @group class:Psc\HTML\Debugger
 */
class DebuggerTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\HTML\Debugger';
    parent::setUp();
    $this->debugger = new Debugger();
  }
  
  public function testConstruct() {
    $tag = new Tag('div');
    
    $this->debugger->container($tag);
    $this->assertNotEmpty($color = \Psc\Preg::qmatch($tag->htmlAttributes(), '/[0-9]+px solid (.*)/'));
    
    $this->debugger->container($tag);
    $this->assertNotEmpty($color2 = \Psc\Preg::qmatch($tag->htmlAttributes(), '/[0-9]+px solid (.*)/'));
    $this->assertNotEquals($color, $color2);
    $this->debugger->container($tag);
    
    $this->assertNotEmpty($color3 = \Psc\Preg::qmatch($tag->htmlAttributes(), '/[0-9]+px solid (.*)/'));
    $this->assertNotEquals($color2, $color3);
    // usw
  }
  
  public function createDebugger() {
    return new Debugger();
  }
}
?>