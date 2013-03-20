<?php

namespace Psc\Doctrine;

class RepositoryTest extends \Psc\Doctrine\DatabaseTestCase {
  
  protected $entityClass;
  protected $emm;
  protected $repository;
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\EntityRepository';
    parent::setUp();
    $this->emm = $this->getEntityManagerMock();
    if (!isset($this->entityClass)) $this->entityClass = 'Psc\Doctrine\TestEntities\Tag';
    
    $this->repository = $this->createRepository(NULL);
  }
  
  public function testConstruct() {
    $this->assertEquals($this->entityClass, $this->repository->getClassName());
  }
  
  protected function createTagUniqueConstraint() {
    return new UniqueConstraint('tag_label', array('label'=>$this->getType('String')));
  }
  
  protected function createQueryBuilderMock($query = NULL) {
    $queryBuilder = $this->doublesManager->createQueryBuilderMock(array('getQuery'), $this->emm);
    if (isset($query)) {
      $queryBuilder->expects($this->atLeastOnce())->method('getQuery') // respect getQuery()->getDQL() in exception
                   ->will($this->returnValue($query));
    }
    return $queryBuilder;
  }

  protected function createRepository(Array $methods = NULL) {
    return $this->emm->getRepositoryMock($this, $this->entityClass, $methods);
  }
}
