<?php

namespace Psc\Data\Type;

/**
 * @group class:Psc\Data\Type\FloatType
 */
class FloatTypeTest extends TestCase {
  
  protected $floatType;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\FloatType';
    parent::setUp();
    $this->floatType = new FloatType();
  }
  
  public function testAcceptance() {
    $this->assertTypeMapsComponent('FloatField', $this->floatType);
  }
}
?>