<?php

namespace Psc\UI\Component;

/**
 * @group class:Psc\UI\Component\PasswordFields
 */
class PasswordFieldsTest extends TestCase {
  
  protected $passwordFields;
  
  public function setUp() {
    $this->componentClass = $this->chainClass = 'Psc\UI\Component\PasswordFields';
    $this->expectedRule = 'Password';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $this->setFixtureValues();
    
    $this->html = $this->component->getHTML();
    $testValue = $this->testValue;

    $pwInput = $this->test->css('input[type="password"][name="testName[password]"]', $this->html)
      ->count(1)
      ->hasAttribute('value', NULL) // nicht vorausfüllen
      ->getJQuery();
    
    $this->assertLabelFor($pwInput->attr('id'), $this->html, $labelText = 'testLabel');

    $confirmInput = $this->test->css('input[type="password"][name="testName[confirmation]"]', $this->html)
      ->count(1)
      ->hasAttribute('value', NULL)
      ->getJQuery();
  }
}
?>