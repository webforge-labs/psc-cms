<?php

namespace Psc\Data\Type;

use Psc\Data\Type\EmailType;

class EmailTypeTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\EmailType';
    parent::setUp();
  }

  public function testConstruct() {
    $this->createEmailType();
  }

  public function createEmailType() {
    return new EmailType();
  }
}
?>