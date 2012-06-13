<?php

namespace Psc\Data\Type;

/**
 * @group class:Psc\Data\Type\DynamicEnumType
 */
class DynamicEnumTypeTest extends \Psc\Code\Test\Base {
  
  protected $dynamicEnumType;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\DynamicEnumType';
    parent::setUp();
    //$this->dynamicEnumType = new DynamicEnumType();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('vom tiptoi acceptance test für gameTypeClass kopieren');
  }
}
?>