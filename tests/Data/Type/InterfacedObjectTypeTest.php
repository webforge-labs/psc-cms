<?php

namespace Psc\Data\Type;

use Psc\Data\Type\InterfacedObjectType;

class InterfacedObjectTypeTest extends \Psc\Code\Test\Base {
  
  protected $type;

  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\InterfacedObjectType';
    parent::setUp();
    $this->type = $this->getMock($this->chainClass, array('getInterface'));
  }

  public function testConstruct() {
    $this->assertChainable($this->type);
  }
  
  /**
   * @expectedException Psc\Data\Type\TypeException
   */
  public function testSetClassNullIsNotAllowed() {
    $this->type->setClass(NULL);
  }

  /**
   * @expectedException Psc\Data\Type\TypeException
   */
  public function testSetClassImplementsWrongInterfaceIsNotAllowed() {
    $this->type->expects($this->any())->method('getInterface')->will($this->returnValue('Psc\Unknown\Interface'));
    
    $this->type->setClass(new \Psc\Code\Generate\GClass('Psc\Data\Type\LinkType'));
  }

  public function testSetClassImplementsRightInterface() {
    $this->type->expects($this->any())->method('getInterface')->will($this->returnValue('Psc\Data\Type\InterfacedType'));
    
    $this->type->setClass(new \Psc\Code\Generate\GClass('Psc\Data\Type\LinkType'));
  }

  public function createInterfacedObjectType() {
    return new InterfacedObjectType();
  }
}
?>