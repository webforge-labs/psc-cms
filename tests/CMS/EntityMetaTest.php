<?php

namespace Psc\CMS;

class EntityMetaTest extends \Psc\Code\Test\Base {
  
  protected $entityMeta;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntityMeta';
    parent::setUp();
    $this->entityMeta = new EntityMeta('tiptoi\Entities\Speaker', 'Sprecher');
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Psc\Code\Generate\GClass',$gClass = $this->entityMeta->getGClass());
    $this->assertEquals('speaker',$this->entityMeta->getEntityName());
    $this->assertEquals('Sprecher',$this->entityMeta->getLabel(\Psc\CMS\TabsContentItem2::LABEL_DEFAULT));
    
    // defaults
    $this->assertEquals(300, $this->entityMeta->getAutoCompleteDelay());
    $this->assertEquals(2, $this->entityMeta->getAutoCompleteMinLength());
  }
}
?>