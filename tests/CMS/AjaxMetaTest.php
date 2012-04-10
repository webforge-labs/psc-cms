<?php

namespace Psc\CMS;

class AjaxMetaTest extends \Psc\Code\Test\Base {
  
  protected $ajaxMeta;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\AjaxMeta';
    parent::setUp();
    $this->ajaxMeta = new AjaxMeta(AjaxMeta::GET,'/person/17/form');
  }
  
  public function testAcceptance() {
    $this->assertEquals('/person/17/form',$this->ajaxMeta->getUrl());
    $this->assertEquals('GET', $this->ajaxMeta->getMethod());
  }
}
?>