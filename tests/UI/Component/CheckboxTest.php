<?php

namespace Psc\UI\Component;

/**
 * @group class:Psc\UI\Component\Checkbox
 */
class CheckboxTest extends TestCase {
  
  public function setUp() {
    $this->componentClass = $this->chainClass = 'Psc\UI\Component\Checkbox';
    parent::setUp();
    $this->testValue = FALSE;
  }
  
  public function testHTML() {
    list($html,$input) = $this->assertStandardInputHTML($testValue = 'true', $type = 'checkbox');
    
    $this->test->css($input)
               ->hasNotAttribute('checked');

    $this->testValue = TRUE;
    list($html,$input) = $this->assertStandardInputHTML($testValue = 'true', $type = 'checkbox');
    
    $this->test->css($input)
               ->hasAttribute('checked','checked');
  }
}
?>