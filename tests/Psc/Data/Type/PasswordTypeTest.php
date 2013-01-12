<?php

namespace Psc\Data\Type;

/**
 * @group class:Psc\Data\Type\PasswordType
 */
class PasswordTypeTest extends TestCase {
  
  protected $passwordType;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\PasswordType';
    parent::setUp();
    $this->passwordType = new PasswordType();
  }
  
  public function testAcceptance() {
    $this->assertTypeMapsComponent('PasswordFields',$this->passwordType);
  }
}
?>