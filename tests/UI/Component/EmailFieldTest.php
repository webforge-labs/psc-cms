<?php

namespace Psc\UI\Component;

use Psc\UI\Component\EmailField;

/**
 * @group component
 */
class EmailFieldTest extends TestCase {

  public function setUp() {
    $this->componentClass = 'Psc\UI\Component\EmailField';
    parent::setUp();
  }

  public function testConstruct() {
    // selbe test wie textField
    $this->createEmailField();
  }

  public function createEmailField() {
    return new EmailField();
  }
}
?>