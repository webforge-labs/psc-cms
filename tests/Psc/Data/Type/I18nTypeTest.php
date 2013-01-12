<?php

namespace Psc\Data\Type;

/**
 * @group class:Psc\Data\Type\I18nType
 */
class I18nTypeTest extends \Psc\Data\Type\TestCase {
  
  protected $i18nType;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\I18nType';
    parent::setUp();
    $this->i18nType = new I18nType(Type::create('String'), array('de','fr'));
  }
  
  public function testMappedComponent() {
    $component = $this->assertTypeMapsComponent('I18nWrapper', $this->i18nType);
    
    $this->assertEquals(array('de','fr'), $component->getLanguages());
  }
}
?>