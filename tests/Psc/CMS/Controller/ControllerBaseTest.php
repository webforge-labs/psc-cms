<?php

namespace Psc\CMS\Controller;

abstract class ControllerBaseTest extends \Psc\Test\DatabaseTestCase {
  
  protected $entityFQN;
  
  protected $repository;
  protected $emm;
  
  protected $controller;

  public function setUp() {
    parent::setUp();
    $this->emm = $this->doublesManager->createEntityManagerMock();
    $this->repository = $this->createRepositoryMock($this->emm, $this->entityFQN);

    $this->controller = $this->createEntityController();
    $this->controller->setOptionalProperties(array('category'));
    $this->setEntityNameInController($this->entityFQN);
  }
  
  
  /**
   * @param entity[]|entity
   */
  protected function expectRepositoryHydrates($entity, $times = NULL) {
    if (is_array($entity)) {
      $entities = \Psc\Doctrine\Helper::reindex($entity);
      $this->repository->expects($times ?: $this->exactly(count($entities)))->method('hydrate')
                     ->with($this->greaterThan(0))
                     ->will($this->returnCallback(function ($identifier) use ($entities) {
                        return $entities[$identifier];
                     }));
      
    } else {
      $this->repository->expects($times ?: $this->once())->method('hydrate')
                     ->with($this->equalTo($entity->getIdentifier()))
                     ->will($this->returnValue($entity));
    }
  }

  protected function expectRepositoryHydratesNot($identifier) {
    $this->repository->expects($this->once())->method('hydrate')
                     ->with($this->equalTo($identifier))
                     ->will($this->throwException(
                        \Psc\Doctrine\EntityNotFoundException::criteria(array('identifier'=>$identifier))
                      ));
  }

  protected function expectRepositoryFinds($entities, Array $by) {
    $this->repository->expects($this->once())->method('findBy')
                     ->with($this->equalTo($by))
                     ->will($this->returnValue($entities));
  }

  protected function expectRepositoryAutoCompletes($entities) {
    $this->repository->expects($this->once())->method('autoCompleteTerm')
                     ->will($this->returnValue($entities));
  }

  protected function expectRepositorySaves($entity) {
    return $this->expectRepositorySavesEqualTo($entity);
  }

  protected function expectRepositorySavesEqualTo($entity) {
    $this->repository->expects($this->once())->method('save')
                     ->with($this->equalTo($entity))
                     ->will($this->returnSelf());
  }

  protected function expectRepositoryPersists($entity, $times = NULL) {
    if (is_array($entity)) {
      $entities = \Psc\Doctrine\Helper::reindex($entity);
      $this->repository->expects($times ?: $this->exactly(count($entities)))->method('persist')
                       ->will($this->returnCallback(function (\Psc\CMS\Entity $entity) use ($entities) {
                          return $entities[$entity->getIdentifier()];
                       }));
    } else {
      $this->repository->expects($times ?: $this->once())->method('persist')
                     ->with($this->equalTo($entity))
                     ->will($this->returnSelf());
    }
  }

  protected function expectRepositoryRemoves($entity) {
    $this->repository->expects($this->once())->method('remove')
                     ->with($this->equalTo($entity))
                     ->will($this->returnSelf());
  }

  protected function expectRepositoryDeletes($entity) {
    $this->repository->expects($this->once())->method('delete')
                     ->with($this->equalTo($entity))
                     ->will($this->returnSelf());
  }
  
  protected function setEntityNameInController($entityName) {
    $this->controller->expects($this->any())->method('getEntityName')
        ->will($this->returnValue($entityName));
  }
  
  protected function createEntityController() {
    $controller = $this->getMock(
      $this->chainClass,
      array(
       'setUp', // wir übersschreiben hier setup, weil wir das repository selbst setzen wollen
       'getEntityName',  // abstract method
       'onContentComponentCreated', // test in FormularTest
       'myCustomAction', // custom action test
       'getLinkRelationsForEntity' // AbstractEntityControllerTest
      ),
      array(
        $this->getTranslationContainer()
      )
    ); 
    $controller->setRepository($this->repository); // some kind of ungeil: schöner: repository in init-Funktion
        
    return $controller;
  }
  
  protected function createRepositoryMock($em, $entityName) {
    return $this->getMock('Psc\Doctrine\EntityRepository',
                   array('hydrate','save','persist','findBy','autoCompleteTerm','remove','delete'),
                   array($em, $this->getMock('Doctrine\ORM\Mapping\ClassMetadata', array(), array($entityName)))
                  );
  }
  
  protected function onNotSuccessfulTest(\Exception $e) {
    if ($e instanceof \Psc\Code\ExceptionExportable) {
      $e->setMessage($e->exportExceptionText());
    }
      
    throw $e;
  }

  protected function getControllerFactory() {
    return $this->getContainer()->getControllerFactory();
  }
}
