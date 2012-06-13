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
    $this->assertInstanceOf('Psc\Data\Type\EnclosingType', $this->enumType);
    $this->assertInstanceOf('Psc\Data\Type\ValidationType', $this->enumType);
  }
  
  public function testComponentMapping() {
    $this->assertTypeMapsComponent('Psc\UI\Component\SelectBox', $this->enumType);
  }
  
  public function testSetTypeIsNotAllowed() {
    $this->setExpectedException('Psc\Exception');
    $this->enumType->setType(Type::create('String'));
  }
}
?>