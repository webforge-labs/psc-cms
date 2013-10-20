<?php

namespace Psc\Data\Type;

use Psc\Data\Type\DateTimeType;

/**
 * @group class:Psc\Data\Type\DateTimeType
 */
class DateTimeTypeTest extends TestCase {

  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\DateTimeType';
    parent::setUp();
  }

  public function testIsAObjectTypeForDateTime() {
    $this->assertObjectType('Webforge\Common\DateTime\DateTime', Type::create('DateTime'));
  }
  
  public function testMapsToComponentDateTimePicker() {
    $this->assertTypeMapsComponent('DateTimePicker', Type::create('DateTime'));
  }
}
