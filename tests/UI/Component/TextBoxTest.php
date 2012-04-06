<?php

namespace Psc\UI\Component;

class TextBoxTest extends TestCase {
  
  public function setUp() {
    $this->componentClass = $this->chainClass = 'Psc\UI\Component\TextBox';
    parent::setUp();
  }
  
  public function testHTML() {
    $this->setFixtureValues();
    
    $html = $this->component->getHTML();
    
    $input = $this->test->css('textarea', $html)
      ->count(1, 'keine textarea gefunden')
      ->hasAttribute('name', 'testName')
      ->hasText($this->testValue)
      ->getJQuery();
    
    $this->assertLabelFor($input->attr('id'), $html);
  }
  
  public function createTextBox() {
    return new TextBox();
  }
}
?>