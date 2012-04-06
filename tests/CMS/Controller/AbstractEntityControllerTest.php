<?php

namespace Psc\CMS\Controller;

/**
 * @TODO testen ob init*() funktionen aufgerufen werden(mit korrekten parametern)
 * @TODO testen ob propertiesOrder bei getEntityFormular benutzt wird
 */
class AbstractEntityControllerTest extends \Psc\Code\Test\Base {
  
  protected $repository;
  protected $emm;
  protected $controller;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\AbstractEntityController';
    parent::setUp();
    $entityName = 'Psc\Doctrine\TestEntities\Article';
    
    $this->emm = $this->doublesManager->createEntityManagerMock();
    $this->loadEntity($entityName);
    
    $this->repository = $this->createRepositoryMock($this->emm, $entityName);
    $this->controller = $this->createEntityController();
    
    $this->setEntityName($entityName);
    $this->article = new \Psc\Doctrine\TestEntities\Article('Lorem Ipsum:', 'Lorem Ipsum Dolor sit amet... <more>');
    $this->article->setId(7);
  }
  
  public function testGetEntity() {
    $this->expectRepositoryHydrates($this->article);
    $this->assertSame($this->article, $this->controller->getEntity($this->article->getIdentifier()));
  }
  
  public function testGetEntity_toFormular() {
    $this->expectRepositoryHydrates($this->article);
    $this->assertInstanceOf('Psc\CMS\EntityFormPanel', $this->controller->getEntity($this->article->getIdentifier(), 'form'));
  }
  
  public function testSaveEntity() {
    $this->expectRepositoryHydrates($this->article);
    $this->expectRepositorySaves($this->article);
    
    $this->controller->setOptionalProperties(array('tags'));
    $return = $this->controller->saveEntity(7,
                                  (object) array(
                                                 'action'=>'1',
                                                 'submitted'=>'true',
                                                 'identifier'=>'7',
                                                 'dataJSON'=>'[]',
                                                 'type'=>$this->article->getEntityName(),
                                                 
                                                 
                                                 'title'=>'blubb',
                                                 'content'=>'content',
                                                 'tags'=>NULL
                                                ),
                                  'form'
                                 );
    
    $this->assertEquals('content', $this->article->getContent());
    $this->assertEquals('blubb', $this->article->getTitle());
    
    $this->assertSame($this->article, $return);
  }
  
  public function testSetRepository() {
    $this->controller->setRepository($otherRep = $this->getMock('Psc\Doctrine\EntityRepository', array(), array(), '', FALSE));
    $this->assertAttributeSame($otherRep, 'repository', $this->controller);
  }
  
  protected function expectRepositoryHydrates($entity) {
    $this->repository->expects($this->once())->method('hydrate')
                     ->with($this->equalTo($entity->getIdentifier()))
                     ->will($this->returnValue($entity));
  }

  protected function expectRepositorySaves($entity) {
    $this->repository->expects($this->once())->method('save')
                     ->with($this->equalTo($entity))
                     ->will($this->returnSelf());
  }
  
  protected function setEntityName($entityName) {
    $this->controller->expects($this->any())->method('getEntityName')
        ->will($this->returnValue($entityName));
  }
  
  protected function createEntityController() {
    $controller = $this->getMock($this->chainClass, array('setUp','getEntityName')); // wir übersschreiben hier setup, weil wir das repository selbst setzen wollen
    $controller->setRepository($this->repository); // some kind of ungeil: schöner: repository in init-Funktion
    
    return $controller;
  }
  
  protected function createRepositoryMock($em, $entityName) {
    return $this->getMock('Psc\Doctrine\EntityRepository',
                   array('hydrate','save'),
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