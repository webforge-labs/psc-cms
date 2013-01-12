<?php

namespace Psc\Doctrine;

/**
 * Ein TestCase zum Testen eines konkreten Repositories
 *
 * anders als der RepositoryTest der Tools zum Mocken von einem Repository für einen anderen Test benutzt
 */
class RepositoryTestCase extends DatabaseTestCase {
  
  protected $repository;
  protected $entityName = NULL;
  
  public function setUp() {
    $this->assertNotEmpty($this->entityName, 'EntityName muss für den RepositoryTestCase gesetzt sein (nur der ClassName)');
    // henne + ei problem hier:
    $entityFQN = $this->entityName;
    $this->chainClass = \Psc\PSC::getProject()->getModule('Doctrine')->getEntitiesNamespace($entityFQN.'Repository');
    parent::setUp();
    $this->repository = $this->em->getRepository($entityFQN);
  }
}
?>