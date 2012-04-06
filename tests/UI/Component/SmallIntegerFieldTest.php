<?php

namespace Psc\UI\Component;

use Psc\UI\Component\SmallIntegerField;

/**
 * @group component
 */
class SmallIntegerFieldTest extends TestCase {

  public function setUp() {
    $this->componentClass = 'Psc\UI\Component\SmallIntegerField';
    parent::setUp();
  }

  public function createSmallIntegerField() {
    return new SmallIntegerField();
  }
}
?>