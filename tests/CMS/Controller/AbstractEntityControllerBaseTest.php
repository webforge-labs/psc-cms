<?php

namespace Psc\CMS\Controller;

/**
 * @group class:Psc\CMS\Controller\AbstractEntityController
 */
abstract class AbstractEntityControllerBaseTest extends \Psc\Code\Test\Base {
  
  protected $repository;
  protected $emm;
  protected $controller;
  
  protected $tags;
  protected $articles;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\AbstractEntityController';
    parent::setUp();
    $entityName = 'Psc\Doctrine\TestEntities\Article';
    
    $this->emm = $this->doublesManager->createEntityManagerMock();
    $this->loadEntity($entityName);
    
    $this->repository = $this->createRepositoryMock($this->emm, $entityName);
    $this->controller = $this->createEntityController();
    $this->controller->setOptionalProperties(array('category'));
    
    $this->setEntityName($entityName);
    $this->article = new \Psc\Doctrine\TestEntities\Article('Lorem Ipsum:', 'Lorem Ipsum Dolor sit amet... <more>');
    $this->assertInstanceOf('Psc\Doctrine\Entity', $this->article);
    $this->article->setId(7);
    
    $this->articles = $this->loadTestEntities('articles');
  }
  
  
  protected function expectRepositoryHydrates($entity) {
    $this->repository->expects($this->once())->method('hydrate')
                     ->with($this->equalTo($entity->getIdentifier()))
                     ->will($this->returnValue($entity));
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
    $this->repository->expects($this->once())->method('save')
                     ->with($this->equalTo($entity))
                     ->will($this->returnSelf());
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
  
  protected function setEntityName($entityName) {
    $this->controller->expects($this->any())->method('getEntityName')
        ->will($this->returnValue($entityName));
  }
  
  protected function createEntityController() {
    $controller = $this->getMock($this->chainClass, array('setUp', // wir übersschreiben hier setup, weil wir das repository selbst setzen wollen
                                                          'getEntityName',  // abstract method
                                                          'onContentComponentCreated', // test in FormularTest
                                                          'myCustomAction' // custom action test
                                                      )); 
    $controller->setRepository($this->repository); // some kind of ungeil: schöner: repository in init-Funktion
    
    
    return $controller;
  }
  
  protected function createRepositoryMock($em, $entityName) {
    return $this->getMock('Psc\Doctrine\EntityRepository',
                   array('hydrate','save','findBy','autoCompleteTerm','remove','delete'),
                   array($em, $this->getMock('Doctrine\ORM\Mapping\ClassMetadata', array(), array($entityName)))
                  );
  }
  
  protected function onNotSuccessfulTest(\Exception $e) {
    if ($e instanceof \Psc\Code\ExceptionExportable) {
      $e->setMessage($e->exportExceptionText());
    }
      
    throw $e;
  }
}
?>