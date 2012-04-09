<?php

namespace Psc\UI\Component;

use Psc\UI\Component\DatePicker;
use Psc\DateTime\DateTime;

/**
 * @group component
 */
class DatePickerTest extends TestCase {

  public function setUp() {
    $this->componentClass = 'Psc\UI\Component\DatePicker';
    parent::setUp();
    $this->testValue = $this->dateTime = new DateTime('21.11.1984 02:00:01');
  }
  
  public function testHTML() {
    $this->assertStandardInputHTML('21.11.1984');
  }
  
  public function testHTML_DateFormat() {
    $this->markTestSkipped('not yet ready');
    $this->component->setDateFormat('d.m.y');
    $this->assertStandardInputHTML('21.11.84');

    $this->component->setDateFormat('d.m:Y');
    $this->assertStandardInputHTML('21.11:1984');
  }
}
?>