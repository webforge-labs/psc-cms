<?php

namespace tiptoi;

class RightContentTest extends \Psc\Code\Test\Base {
  
  protected $rightContent;
  
  public function setUp() {
    $this->chainClass = 'tiptoi\RightContent';
    parent::setUp();
    $this->rightContent = new RightContent();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>