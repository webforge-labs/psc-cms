<?php

namespace Psc\UI\Component;

/**
 * @group class:Psc\UI\Component\FloatField
 */
class FloatFieldTest extends TestCase {
  
  protected $floatField;
  
  public function setUp() {
    $this->componentClass = 'Psc\UI\Component\FloatField';
    parent::setUp();
    $this->testValue = 1192.23;
    $this->floatField = new FloatField();
  }
  
  public function testAcceptance() {
    $this->setFixtureValues();
    
    $this->assertStandardInputHTML('1192,23');
  }
  
  public function testNotDoubleColonInThousands() {
    $this->testValue = 1192;
    $this->setFixtureValues();
    $this->assertStandardInputHTML('1192,00');
  }
}
?>