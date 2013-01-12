<?php

namespace Psc\UI\Component;

use Psc\UI\Component\TextField;

/**
 * @group component
 * @group class:Psc\UI\Component\TextField
 */
class TextFieldTest extends TestCase {

  public function setUp() {
    $this->componentClass = 'Psc\UI\Component\TextField';
    parent::setUp();
  }
  
  public function testConstruct() {
    $this->assertInstanceof($this->componentClass, $this->component);
  }
  
  public function testHTML() {
    $this->assertStandardInputHTML();
  }
}
?>