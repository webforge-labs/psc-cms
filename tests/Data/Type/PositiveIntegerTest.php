<?php

namespace Psc\Data\Type;

class PositiveIntegerTypeTest extends \Psc\Data\Type\IntegerTypeTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\PositiveIntegerType';
    parent::setUp();
  }
  
  public function testConstruct() {
    return $this->createPositiveInteger();
  }
  
  public function createPositiveInteger() {
    return new PositiveIntegerType();
  }
}
?>