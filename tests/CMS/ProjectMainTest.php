<?php

namespace Psc\CMS;

class ProjectMainTest extends \Psc\Code\Test\Base {
  
  protected $main;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\ProjectMain';
    parent::setUp();
    
    $this->main = new ProjectMain(); // das geht, total krank, aber das macht ALLE injection
  }
  
  public function testDependenciesClasses() {
    $this->main->init();
    
    $this->assertInstanceOf('Psc\CMS\CMS', $this->main);
    $this->assertInstanceof('Psc\Environment', $this->main->getEnvironment());
    $this->assertInstanceof('Psc\CMS\Project', $project = $this->main->getProject());
    
    $this->assertInstanceOf('Psc\Net\HTTP\FrontController', $frontController = $this->main->getFrontController());
      $this->assertInstanceOf('Psc\Net\HTTP\RequestHandler', $frontController->getRequestHandler());
      $this->assertInstanceOf('Psc\CMS\ResourceHandler', $resourceHandler = $frontController->getResourceHandler());
      $this->assertSame($project, $resourceHandler->getResourceManager()->getProject());
    
    $this->assertInstanceOf('Psc\Doctrine\DCPackage', $this->main->getDoctrinePackage());
    $this->assertInstanceOf('Doctrine\ORM\EntityManager', $this->main->getEntityManager());
      $this->assertSame($this->main->getDoctrinePackage()->getEntityManager(), $this->main->getEntityManager());
    $this->assertInstanceOf('Psc\CMS\Service\EntityService', $entityService = $this->main->getEntityService());
      $this->assertSame($project, $entityService->getProject());
      $this->assertAttributeSame($this->main->getDoctrinePackage(), 'dc', $entityService);
      
      
    $this->assertInstanceOf('Psc\CMS\RightContent', $this->main->getRightContent());
    
    $this->assertInternalType('integer', $this->main->getDebugLevel());
  }
  
  public function testUserManagerCall() {
    if (getenv('PEGASUS_CI')) {
      $this->markTestSkipped('kein lokaler webserver am start');
    }
    $this->main->init();
    $this->assertCount(2, $this->main->getFrontController()->getRequestHandler()->getServices(), 'Ist EntityService hinzugefügt worden?');
    $this->response = $this->request('GET', '/entities/users/grid');
    
    $this->assertResponse(200);
  }
  
  protected function request($method, $resource) {
    $fc = $this->main->getFrontController();
    $fc->init($this->request = $this->doublesManager->createHTTPRequest($method, $resource));
    return $fc->handle($this->request);
  }
  
  protected function assertResponse($code = 200) {
    $this->assertEquals($code, $this->response->getCode(), $this->response->debug());
  }
}
?>