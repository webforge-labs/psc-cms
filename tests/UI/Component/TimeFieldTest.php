<?php

namespace Psc\UI\Component;

use Psc\DateTime\DateTime;

/**
 * @group class:Psc\UI\Component\TimeField
 */
class TimeFieldTest extends TestCase {
  
  public function setUp() {
    $this->componentClass = $this->chainClass = 'Psc\UI\Component\TimeField';
    parent::setUp();
    $this->testValue = new DateTime('21.11.1983 02:00:01');
  }
  
  public function testHTML() {
    $this->assertStandardInputHTML('02:00');
  }
}
?>