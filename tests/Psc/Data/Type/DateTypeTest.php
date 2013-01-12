<?php

namespace Psc\Data\Type;

/**
 * @group class:Psc\Data\Type\DateType
 */
class DateTypeTest extends TestCase {
  
  protected $dateType;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\DateType';
    parent::setUp();
    $this->dateType = new DateType();
  }
  
  public function testMapsToComponentDatePicker() {
    $this->assertTypeMapsComponent('DatePicker', $this->dateType);
  }

  public function testIsAObjectTypeForDate() {
    $this->assertObjectType('Psc\DateTime\Date', $this->dateType);
  }
}
?>