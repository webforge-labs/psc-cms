<?php

namespace Psc\Doctrine;

/**
 * @group class:Psc\Doctrine\EntityRepository
 */
class EntityRepositoryTest extends \Psc\Doctrine\RepositoryTest {
  
  public function setUp() {
    $this->entityClass = 'Psc\Doctrine\TestEntities\Tag';
    parent::setUp();
  }
  
  public function testInstanceOfDoctrineEntityRepository() {
    $this->assertInstanceOf('Doctrine\ORM\EntityRepository',$this->repository);
  }
}
