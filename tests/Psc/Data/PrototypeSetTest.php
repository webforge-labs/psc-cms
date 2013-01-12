<?php

namespace Psc\Data;

/**
 * @group class:Psc\Data\PrototypeSet
 */
class PrototypeSetTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\PrototypeSet';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $set = new PrototypeSet();
    $set->set('fieldOne','v1',$this->getType('String'));
    $set->set('fieldTwo',2,$this->getType('Integer'));
    
    $this->assertEquals('v1', $set->getFieldOne());
    $this->assertEquals(2, $set->getFieldTwo());
    
    $this->assertChainable($set->setFieldOne('banane'));
    $this->assertEquals('banane', $set->get('fieldOne'));
  }
}
?>