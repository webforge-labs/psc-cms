<?php

namespace Psc\UI\Component;

class ComboDropBoxTest extends TestCase {
  
  public function setUp() {
    $this->markTestIncomplete('@TODO: Hier ein Beispiel bauen');

    $this->componentClass = $this->chainClass = 'Psc\UI\Component\FormComboDropBox';
    parent::setUp();
  }
  
  public function testHTML() {
    $html = $this->component->getHTML();
    
    $input = $this->test->css('textarea', $html)
      ->count(1, 'keine textarea gefunden')
      ->hasAttribute('name', 'testName')
      ->hasText($this->testValue)
      ->getJQuery();
    
    $this->assertLabelFor($input->attr('id'), $html);
    
  }
  
  public function createFormComboDropBox() {
    return new FormComboDropBox();
  }
}
?>