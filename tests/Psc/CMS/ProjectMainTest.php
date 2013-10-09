<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\ProjectMain
 */
class ProjectMainTest extends \Psc\Code\Test\Base {
  
  protected $main;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\ProjectMain';
    parent::setUp();
    
    $this->main = new ProjectMain(); // das geht, total krank, aber das macht ALLE injection
    $this->main->session = $this->getMock('Psc\Session\Session');
    $this->main->setContainerClass('Psc\Test\CMS\Container');
    $this->request = $this->doublesManager->createHTTPRequest('GET','/');
  }
  
  public function testDependenciesClasses() {
    $this->main->init($this->request);
    
    $this->assertInstanceOf('Psc\CMS\ProjectMain', $this->main);
    $this->assertInstanceof('Psc\Environment', $this->main->getEnvironment());
    $this->assertInstanceof('Webforge\Framework\Project', $project = $this->main->getProject());
    
    $this->assertInstanceOf('Psc\Net\HTTP\FrontController', $frontController = $this->main->getFrontController());
      $this->assertInstanceOf('Psc\Net\HTTP\RequestHandler', $frontController->getRequestHandler());
    
    $this->assertInstanceOf('Psc\Doctrine\DCPackage', $this->main->getDoctrinePackage());
    $this->assertInstanceOf('Doctrine\ORM\EntityManager', $this->main->getEntityManager());
      $this->assertSame($this->main->getDoctrinePackage()->getEntityManager(), $this->main->getEntityManager());
    $this->assertInstanceOf('Psc\CMS\Service\EntityService', $entityService = $this->main->getEntityService());
      $this->assertSame($project, $entityService->getProject());
      $this->assertAttributeSame($this->main->getDoctrinePackage(), 'dc', $entityService);
      
      
    $this->assertInstanceOf('Psc\CMS\RightContent', $this->main->getRightContent());
    
    $this->assertInternalType('integer', $this->main->getDebugLevel());

    $this->assertInstanceOf('Psc\CMS\Roles\SimpleContainer', $this->main->getContainer());
    $this->assertInstanceOf('Psc\CMS\Controller\Factory', $this->main->getControllerFactory());
  }

  public function testAuthControllerGetsEntityManagerInjected_Regression() {
    // pre condition: this stupid tests uses the default entityManager
    $this->em = $this->main->getDoctrinePackage()->getModule()->getEntityManager('tests');

    // set to other em for a good test
    $this->main->getDoctrinePackage()->setEntityManager($this->em);

    // inject with new em
    $authController = $this->main->getAuthController();
    $this->assertInstanceOf('Psc\CMS\UserManager', $userManager = $authController->getAuth()->getUserManager());

    $this->assertAttributeSame(
      $this->em->getRepository('Psc\Entities\User'),
      'repository',
      $userManager,
      'repository injected in userManager should be the same as in the test'
    );
  }
  
  
  public function testUserManagerCall() {
    if (\Psc\PSC::isTravis()) {
      $this->markTestSkipped('kein lokaler webserver am start');
    }
    $this->main->init($this->request);
    $this->assertCount(2, $this->main->getFrontController()->getRequestHandler()->getServices(), 'Ist EntityService hinzugefügt worden?');
    $this->response = $this->request('GET', '/entities/users/grid');
    
    $this->assertResponse(200);
  }

  public function testWelcomeTemplateHasInternationalTitle() {
    $welcome = $this->main->getWelcomeTemplate();

    $welcome->setLanguage('de');
    $this->assertEquals('Willkommen', $welcome->__('title'));

    $welcome->setLanguage('en');
    $this->assertEquals('Welcome', $welcome->__('title'));
  }
  
  protected function request($method, $resource) {
    $fc = $this->main->getFrontController();
    $fc->init($this->request = $this->doublesManager->createHTTPRequest($method, $resource));
    return $fc->handle($this->request);
  }
  
  protected function assertResponse($code = 200) {
    $this->assertEquals($code, $this->response->getCode(), $this->request->debug()."\n".$this->response->debug());
  }
}
