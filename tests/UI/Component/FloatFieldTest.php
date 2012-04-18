<?php

namespace Psc\UI\Component;

class FloatFieldTest extends TestCase {
  
  protected $floatField;
  
  public function setUp() {
    $this->componentClass = 'Psc\UI\Component\FloatField';
    parent::setUp();
    $this->testValue = 192.23;
    $this->floatField = new FloatField();
  }
  
  public function testAcceptance() {
    $this->setFixtureValues();
    
    $this->assertStandardInputHTML(192.23);
  }
}
?>