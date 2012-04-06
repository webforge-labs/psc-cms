<?php

namespace Psc\Data\Type;

class PersistentCollectionTypeTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\PersistentCollectionType';
    parent::setUp();
  }
  
  public function testConstruct() {
    $pct = new PersistentCollectionType(new \Psc\Code\Generate\GClass('Psc\Doctrine\TestEntities\Tag'));
    $this->assertInstanceOf('Psc\Data\Type\CollectionType', $pct);
    
    $this->assertInstanceOf('Psc\Data\Type\ObjectType', $pct->getType());
    $this->assertEquals('Psc\Doctrine\TestEntities\Tag',$pct->getType()->getClass()->getFQN());
  }
}
?>