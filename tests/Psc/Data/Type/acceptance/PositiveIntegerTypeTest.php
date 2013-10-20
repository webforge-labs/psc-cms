<?php

namespace Psc\Data\Type;

/**
 * @group class:Psc\Data\Type\PositiveIntegerType
 */
class PositiveIntegerTypeTest extends IntegerTypeTestCase {
  
  protected $positiveIntegerType;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\PositiveIntegerType';
    parent::setUp();
    $this->positiveIntegerType = new PositiveIntegerType();
  }

  public function testConstruct() {
    $type = new PositiveIntegerType();
    
    return $type;
  }
  
  public function testMapsComponent() {
    $this->assertTypeMapsComponent('any', $this->positiveIntegerType);
  }
  
}
?>