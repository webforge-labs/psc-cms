<?php

namespace Psc\Data\Type;

use Psc\Data\Type\DateType;

class DateTypeTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\DateType';
    parent::setUp();
  }

  public function testConstruct() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }

  public function createDateType() {
    return new DateType();
  }
}
?>