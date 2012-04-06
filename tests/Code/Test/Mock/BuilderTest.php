<?php

namespace Psc\Code\Test\Mock;

class BuilderTest extends \Psc\Code\Test\Base {
  
  protected $builder;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Test\Mock\Builder';
    parent::setUp();
    $this->builder = new Builder();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('Stub vom Test-Creater');
  }
}
?>