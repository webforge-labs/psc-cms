<?php

namespace Psc\Doctrine\Mocks;

use Psc\Data\ArrayCollection;

/**
 * @group class:Psc\Doctrine\Mocks\EntityManager
 */
class EntityManagerTest extends \Psc\Doctrine\DatabaseTestCase {
  
  protected $emm; // EntityManagerMock
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\Mocks\EntityManager';
    parent::setUp();
    $this->emm = new \Psc\Doctrine\Mocks\EntityManager();
  }
  
  /**
   * Damit unser Test schöner wird, hat der em eine getMockCollection generic function
   */
  public function testGetMockCollection() {
    $this->assertSame($this->emm->getPersisted(), $this->emm->getMockCollection('persisted'));
    $this->assertSame($this->emm->getRemoved(), $this->emm->getMockCollection('removed'));
    $this->assertSame($this->emm->getDetached(), $this->emm->getMockCollection('detached'));
    $this->assertSame($this->emm->getMerged(), $this->emm->getMockCollection('merged'));
  }
  
  /**
   * @dataProvider provideMockCollections
   */
  public function testConstruct($colls) {
    $this->assertInstanceOf('Doctrine\ORM\EntityManager', $this->emm);
    
    foreach ($colls as $coll) {
      $this->assertEmptyLogCollection($this->emm->getMockCollection($coll));
    }
  }
  
  /**
   * @dataProvider provideMockCollections
   */
  public function testSetMockCollection($colls) {
    $entity = $this->createTestEntity();
    foreach ($colls as $coll) {
      $set = \Psc\Code\Code::castSetter($coll);
      $this->assertChainable($set($this->emm, array($entity)));
      $this->assertEquals(array($entity), $this->emm->getMockCollection($coll)->toArray());
    }
  }
  
  
  public function testPersist() {
    $this->emm->persist($entity = $this->createTestEntity());
    $this->assertCount(1,$this->emm->getPersisted());
  }

  public function testRemove() {
    $this->emm->persist($entity = $this->createTestEntity());
    $this->emm->remove($entity);
    
    $this->assertCount(0,$this->emm->getPersisted());
    $this->assertCount(0,$this->emm->getDetached());
    $this->assertCount(1,$this->emm->getRemoved());
  }

  public function testMerge() {
    $this->emm->merge($entity = $this->createTestEntity());
    $this->assertCount(1, $this->emm->getMerged());
  }
  
  public function testDetach_removesFromPersisted() {
    $this->emm->persist($entity = $this->createTestEntity());
    $this->emm->detach($entity);
    
    $this->assertCount(1, $this->emm->getDetached());
    $this->assertCount(0, $this->emm->getPersisted());
  }

  public function testDetach_removesFromPersisted_removesFromRemoved() {
    $this->emm->persist($entity = $this->createTestEntity());
    $this->emm->remove($entity);
    $this->emm->detach($entity);
    
    $this->assertCount(0,$this->emm->getPersisted());
    $this->assertCount(0,$this->emm->getRemoved());
    $this->assertCount(1,$this->emm->getDetached());
  }

  public function testRemove_removesFromDetached() {
    $this->emm->persist($entity = $this->createTestEntity());
    $this->emm->detach($entity);
    $this->emm->remove($entity);

    $this->assertCount(0,$this->emm->getPersisted());
    $this->assertCount(0,$this->emm->getDetached());
    $this->assertCount(1,$this->emm->getRemoved());
  }
  
  
  /**
   * @dataProvider provideMockCollections
   */
  public function testClear_ClearsAllExceptDetach($colls) {
    $this->emm->persist($entity = $this->createTestEntity());
    $this->emm->persist($entity2 = $this->createTestEntity());
    $this->emm->remove($entity3 = $this->createTestEntity());
    $this->emm->remove($entity4 = $this->createTestEntity());
    $this->emm->detach($entity);
    
    $this->emm->merge($entity5 = $this->createTestEntity());
    
    $this->emm->clear();
    
    $this->assertCount(4, $this->emm->getDetached()); // $entity5 von merge zählt nicht!
    foreach ($colls as $coll) {
      if ($coll === 'detached') continue;
      $this->assertCount(0, $this->emm->getMockCollection($coll), sprintf("MockCollection: '%s' ist nicht leer", $coll));
    }
  }
  
  public function testFlushGetsCounted() {
    $this->assertEquals(0,$this->emm->getFlushs());
    $this->emm->flush();
    $this->assertEquals(1, $this->emm->getFlushs());
  }
  
  public function testSetFlushs() {
    $this->assertEquals(1, $this->emm->setFlushs(1)->getFlushs());
  }
  
  public function testDoublesManagerEntityManager_GetsRepositoryMock() {
    $repository = $this->getEntityManagerMock()->getRepositoryMock($this, 'Psc\Doctrine\TestEntities\Tag');
    $this->assertInstanceOf('Psc\Doctrine\EntityRepository', $repository);
    $this->assertInstanceOfMock($repository);
  }
  
  protected function assertEmptyLogCollection($collection) {
    $this->assertInstanceOf('Psc\Data\ArrayCollection', $collection);
    $this->assertCount(0,$collection);
  }
  
  public static function provideMockCollections() {
    return Array(
      array(array('remove'=>'removed','perist'=>'persisted','detach'=>'detached','merge'=>'merged'))
    );
  }
  
  public function createTestEntity() {
    return new \Psc\Doctrine\TestEntities\Tag('nice');
  }
}
?>