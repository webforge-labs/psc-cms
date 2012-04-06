<?php

namespace Psc\Data\Type;

use Psc\Code\Generate\GClass;

class CollectionTypeTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\CollectionType';
    parent::setUp();
  }

  public function testConstruct() {
    return \Psc\Data\Type\Type::create('Collection');
  }
  
  public function testImplementationConstruct() {
    $type = \Psc\Data\Type\Type::create('Collection', CollectionType::PSC_ARRAY_COLLECTION);
    $this->assertEquals('Psc\Data\ArrayCollection',$type->getClass()->getFQN());

    $type = \Psc\Data\Type\Type::create('Collection', CollectionType::DOCTRINE_ARRAY_COLLECTION);
    $this->assertEquals('Doctrine\Common\Collections\ArrayCollection',$type->getClass()->getFQN());
  }
  
  public function testImplementationInnerTypeConstruct() {
    $type = \Psc\Data\Type\Type::create('Collection', CollectionType::DOCTRINE_ARRAY_COLLECTION, new ObjectType(new GClass('Psc\Doctrine\Entity')));
    $this->assertEquals('Doctrine\Common\Collections\ArrayCollection',$type->getClass()->getFQN());
    $this->assertTrue($type->isTyped());
    $this->assertInstanceOf('Psc\Data\Type\ObjectType',$type->getType());
    $this->assertEquals('Psc\Doctrine\Entity',$type->getType()->getClassFQN());
    return $type;
  }
  
  /**
   * @depends testImplementationInnerTypeConstruct
   */
  public function testgetPHPType($type) {
    $this->assertEquals('Doctrine\Common\Collections\Collection<Psc\Doctrine\Entity>',$type->getPHPType());
  }

  /**
   * @depends testConstruct
   */
  public function testInterfaced($link) {
    $this->assertInstanceOf('Psc\Data\Type\InterfacedType', $link);
    $this->assertTrue(interface_exists($link->getInterface()), 'Interface: '.$link->getInterface().' existiert nicht');
    $this->assertEquals('Doctrine\Common\Collections\Collection', $link->getInterface());
    $this->assertEquals('Doctrine\Common\Collections\Collection', $link->getPHPHint());
  }

  public function createCollectionType() {
    return new CollectionType();
  }
}
?>