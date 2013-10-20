<?php

namespace Psc\Data\Type;

/**
 * @group class:Psc\Data\Type\EnumType
 */
class EnumTypeTest extends TestCase {
  
  protected $enumType;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\EnumType';
    parent::setUp();
    $this->enumType = new EnumType(Type::create('String'), array('v1','v2'));
  }
  
  public function testInterfaces() {
    // enclosing: weil wir als Enum auf einen Template-Type projezieren können
    $this->assertInstanceOf('Webforge\Types\EnclosingType', $this->enumType);
    $this->assertInstanceOf('Webforge\Types\ValidationType', $this->enumType);
  }
  
  public function testComponentMapping() {
    $this->assertTypeMapsComponent('Psc\UI\Component\SelectBox', $this->enumType);
  }
  
  public function testSetTypeIsNotAllowed() {
    $this->setExpectedException('Webforge\Common\Exception');
    $this->enumType->setType(Type::create('String'));
  }
}
?>