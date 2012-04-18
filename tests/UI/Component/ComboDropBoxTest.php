<?php

namespace Psc\UI\Component;

class ComboDropBoxTest extends TestCase {
  
  public function setUp() {
    $this->markTestSkipped('@TODO: Work in Progress');

    $this->componentClass = $this->chainClass = 'Psc\UI\Component\ComboDropBox';
    parent::setUp();
  }
  
  public function testHTML() {
    // html wird dasselbe sein wie bei Psc\UI\ComboDropBox2 ;)
    // höchstens testen ob es gewrapped ist
    
  }
  
  public function createFormComboDropBox() {
    return new ComboDropBox();
  }
}
?>