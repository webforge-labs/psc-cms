<?php

namespace Psc\HTML;

class WidgetBaseTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\HTML\WidgetBase';
    parent::setUp();
  }
  
  public function testConstruct() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
  
  public function createWidgetBase() {
    return new WidgetBase();
  }
}
?>