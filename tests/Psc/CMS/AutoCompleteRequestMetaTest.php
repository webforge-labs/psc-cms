<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\AutoCompleteRequestMeta
 */
class AutoCompleteRequestMetaTest extends \Psc\Code\Test\Base {
  
  protected $acRequestMeta;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\AutoCompleteRequestMeta';
    parent::setUp();
    
    $this->acRequestMeta = $this->getEntityMeta('Psc\Doctrine\TestEntities\Person')->getAutoCompleteRequestMeta();
  }
  
  public function testAcceptance() {
    $this->assertNotEmpty($this->acRequestMeta->getUrl());
    $this->assertNotEmpty($this->acRequestMeta->getMethod());
    $this->assertEquals(300,$this->acRequestMeta->getDelay());
    $this->assertEquals(2,$this->acRequestMeta->getMinLength());
    $this->assertNull($this->acRequestMeta->getMaxResults());

    $this->acRequestMeta
      ->setMinLength(0)
      ->setDelay(200)
      ->setMaxResults(15)
    ;
    
    $this->assertEquals(200,$this->acRequestMeta->getDelay());
    $this->assertEquals(0,$this->acRequestMeta->getMinLength());
    $this->assertEquals(15,$this->acRequestMeta->getMaxResults());
  }
}
?>