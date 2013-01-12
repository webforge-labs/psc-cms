<?php

namespace Psc\HTML;

/**
 * @group class:Psc\HTML\Base
 */
class BaseTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\HTML\Base';
    parent::setUp();
    $this->html = $this->getMock($this->chainClass, array('doInit'));
  }
  
  public function testDoInitGetsNotCalledTwice() {
    $this->html->expects($this->once())->method('doInit')->will($this->returnSelf());
    $this->html->html();
    $this->html->html();
  }
}
?>