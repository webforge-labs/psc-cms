<?php

namespace Psc\Data\Type;

use Psc\Data\Type\BirthdayType;

class BirthdayTypeTest extends TestCase {

  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\BirthdayType';
    parent::setUp();
  }
  
  public function testMaps() {
    $this->assertTypeMapsComponent('Psc\UI\Component\BirthdayPicker', new BirthdayType());
  }
}
?>