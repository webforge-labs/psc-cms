<?php

namespace Psc\UI\Component;

use Psc\UI\Component\IntegerField;

/**
 * @group component
 * @group class:Psc\UI\Component\IntegerField
 */
class IntegerFieldTest extends TestCase {

  public function setUp() {
    $this->componentClass = 'Psc\UI\Component\IntegerField';
    parent::setUp();
  }

  public function createIntegerField() {
    return new IntegerField();
  }
}
?>