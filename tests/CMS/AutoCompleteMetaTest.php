<?php

namespace Psc\CMS;

class AutoCompleteMetaTest extends \Psc\Code\Test\Base {
  
  protected $autoCompleteMeta;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\AutoCompleteMeta';
    parent::setUp();
    
  }
  
  public function testAcceptance() {
    $this->autoCompleteMeta = new AutoCompleteMeta(AutoCompleteMeta::GET,'/some/entities/list', 1, 500);
    
    $this->assertEquals('/some/entities/list',$this->autoCompleteMeta->getUrl());
    $this->assertEquals('GET',$this->autoCompleteMeta->getMethod());
    $this->assertEquals(500,$this->autoCompleteMeta->getDelay());
    $this->assertEquals(1,$this->autoCompleteMeta->getMinLength());

    $this->autoCompleteMeta
      ->setMinLength(2)
      ->setDelay(300)
    ;
    
    $this->assertEquals(300,$this->autoCompleteMeta->getDelay());
    $this->assertEquals(2,$this->autoCompleteMeta->getMinLength());
  }
}
?>