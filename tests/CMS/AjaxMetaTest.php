<?php

namespace Psc\CMS;

class AjaxMetaTest extends \Psc\Code\Test\Base {
  
  protected $ajaxMeta;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\AjaxMeta';
    parent::setUp();
    $this->ajaxMeta = new AjaxMeta();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>