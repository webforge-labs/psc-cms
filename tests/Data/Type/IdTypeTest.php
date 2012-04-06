<?php

namespace Psc\Data\Type;

class IdTypeTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\IdType';
    parent::setUp();
  }
  
  public function testConstruct() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
  
  public function createIdType() {
    return new IdType();
  }
}
?>