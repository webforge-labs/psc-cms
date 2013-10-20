<?php

namespace Psc\Data\Type;

/**
 * @group class:Psc\Data\Type\MixedType
 */
class MixedTypeTest extends TestCase {
  
  protected $mixedType;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\MixedType';
    parent::setUp();
    $this->mixedType = new MixedType();
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Webforge\Types\PseudoType', $this->mixedType);
    $this->assertDocType('mixed', $this->mixedType);
  }
}
?>