<?php

namespace Psc\Gedmo;

/**
 * @group class:Psc\Gedmo\FieldTranslation
 */
class FieldTranslationTest extends \Psc\Code\Test\Base {
  
  protected $fieldTranslation;
  
  public function setUp() {
    $this->chainClass = 'Psc\Gedmo\FieldTranslation';
    parent::setUp();
    $this->fieldTranslation = $this->getMockForAbstractClass($this->chainClass, array('de','title','Ein Deutscher Testtitel'));
  }

  public function testAcceptance() {
    $this->assertEquals('de',$this->fieldTranslation->getLocale());
    $this->assertEquals('title',$this->fieldTranslation->getField());
    $this->assertEquals('Ein Deutscher Testtitel',$this->fieldTranslation->getContent());
  }
}
?>