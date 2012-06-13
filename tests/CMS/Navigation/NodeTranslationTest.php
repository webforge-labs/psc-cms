<?php

namespace Psc\CMS\Navigation;

/**
 * @group class:Psc\CMS\Navigation\NodeTranslation
 */
class NodeTranslationTest extends \Psc\Code\Test\Base {
  
  protected $nodeTranslation;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Navigation\NodeTranslation';
    parent::setUp();
    $this->nodeTranslation = $this->getMockForAbstractClass($this->chainClass, array('de','title','Ein Deutscher Testtitel'));
  }
  
  public function testAcceptance() {
    $this->assertEquals('de',$this->nodeTranslation->getLocale());
    $this->assertEquals('title',$this->nodeTranslation->getField());
    $this->assertEquals('Ein Deutscher Testtitel',$this->nodeTranslation->getContent());
  }
}
?>