<?php

namespace Psc\CMS;

class ContextTabsContentItemTest extends \Psc\Code\Test\Base {
  
  protected $contextTabsContentItem;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\ContextTabsContentItem';
    parent::setUp();
    $this->contextTabsContentItem = new ContextTabsContentItem();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>