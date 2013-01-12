<?php

namespace Psc\UI\Component;

/**
 * @group class:Psc\UI\Component\PasswordField
 */
class PasswordFieldTest extends TestCase {
  
  public function setUp() {
    $this->componentClass = $this->chainClass = 'Psc\UI\Component\PasswordField';
    parent::setUp();
  }

  public function testConstruct() {
    $this->assertInstanceof($this->componentClass, $this->component);
  }

  public function testHTML() {
    $this->assertStandardInputHTML('testValue', 'password');
  }
}
?>