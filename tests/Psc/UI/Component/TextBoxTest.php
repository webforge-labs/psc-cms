<?php

namespace Psc\UI\Component;

/**
 * @group class:Psc\UI\Component\TextBox
 */
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
      ->hasAttribute('cols')
      ->hasAttribute('rows')
      ->hasStyle('width','90%')
      ->hasText($this->testValue)
      ->getJQuery();
    
    $this->assertLabelFor($input->attr('id'), $html);
  }

  
  public function testCaptureTabAddsJavascriptAcceptance() {
    $component = $this->createComponent();
    $component->setCapturesTab(TRUE);
    
    $this->html = (string) $component->getHTML();
    
    $this->assertContains("component.on('keydown', function (e) {", $this->html);
  }
  
  public function createTextBox() {
    return new TextBox();
  }
}
?>