<?php

namespace Psc\Data\Type;

use Psc\Data\Type\EmailType;

/**
 * @group class:Psc\Data\Type\EmailType
 */
class EmailTypeTest extends TestCase {

  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\EmailType';
    parent::setUp();
  }

  public function testConstruct() {
    $this->createEmailType();
  }

  public function testMapsComponent() {
    $this->assertTypeMapsComponent('EmailField', new EmailType);
  }

  public function createEmailType() {
    return new EmailType();
  }
}
?>