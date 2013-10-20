<?php

namespace Psc\Data\Type;

/**
 * @group class:Psc\Data\Type\IdType
 */
class IdTypeTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\IdType';
    parent::setUp();
    $this->type = new IdType();
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Webforge\Types\IntegerType',$this->type);
  }

}
?>