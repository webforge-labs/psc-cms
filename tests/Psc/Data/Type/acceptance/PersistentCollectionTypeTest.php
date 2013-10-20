<?php

namespace Psc\Data\Type;

/**
 * @group class:Psc\Data\Type\PersistentCollectionType
 */
class PersistentCollectionTypeTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\PersistentCollectionType';
    parent::setUp();
  }
  
  public function testConstruct() {
    $pct = new PersistentCollectionType(new \Psc\Code\Generate\GClass('Psc\Doctrine\TestEntities\Tag'));
    $this->assertInstanceOf('Webforge\Types\CollectionType', $pct);
    
    $this->assertInstanceOf('Webforge\Types\ObjectType', $pct->getType());
    $this->assertEquals('Psc\Doctrine\TestEntities\Tag',$pct->getType()->getClass()->getFQN());
  }
}
?>