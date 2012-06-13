<?php

namespace Psc\CMS;

use Psc\Environment;
use Psc\PSC;

/**
 * @group class:Psc\CMS\CMS
 */
class CMSTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\CMS';
    parent::setUp();
    
    $this->jsManager = $this->getMock('Psc\JS\ProxyManager', array(), array(), '', FALSE);
    $this->cssManager = $this->getMock('Psc\CSS\Manager', array(), array(), '', FALSE);
    $this->user = $this->createUser();
    $this->authController = $this->createAuthController($this->user);
    $this->environment = new Environment();
    $this->project = PSC::getProject();
    
    // inject a lot
    $this->cms = new CMS($this->environment, $this->project);
    $this->cms->setJSManager($this->jsManager);
    $this->cms->setCSSManager($this->cssManager);
    $this->cms->setAuthController($this->authController);
    
    $this->cms->init();
  }
  
  public function testConstruct() {
    $this->assertSame($this->authController, $this->cms->getAuthController());
    $this->assertSame($this->jsManager, $this->cms->getJSManager());
    $this->assertSame($this->cssManager, $this->cms->getCSSManager());
    $this->assertSame($this->cssManager, $this->cms->getCSSManager());
    $this->assertSame($this->environment, $this->cms->getEnvironment());
    $this->assertSame($this->user, $this->authController->getUser());
  }
  
  public function testAuthControllerIsRunOnAuth() {
    $this->authController
      ->expects($this->once())
      ->method('run');
      
    $this->assertInstanceOf('Psc\CMS\Controller\AuthController',$this->cms->auth());
  }
  
  public function testMainHTMLPageGetsInjected() {
    $this->cms->auth(); // damit user gesetzt ist, sollte das vll mal eine exception schmeissen? (wenn getMainHTMLPage vor auth kommt?)
    
    $page = $this->cms->getMainHTMLPage();
    $this->assertInstanceOf('Psc\CMS\HTMLPage', $page);
    
    $samePage = $this->cms->getMainHTMLPage(); // wird nur einmal erstellt
    $this->assertSame($page,$samePage);
    
    $this->assertSame($this->cssManager, $page->getCSSManager());
    $this->assertSame($this->jsManager, $page->getJSManager());
    
    // hier könnten wir noch als acceptance jede menge html asserten
  }
  
  protected function createAuthController($user) {
    $authController = $this->getMock('Psc\CMS\Controller\AuthController', array('run','setUserClass','getUser'), array(), '', FALSE);
    
    $authController->expects($this->any())
        ->method('getUser')
        ->will($this->returnValue($user));
    
    return $authController;
  }
  
  protected function createUser() {
    $user = new \Entities\User();
    $user->setEmail('p.scheit@ps-webforge.com');
    $user->setPassword('blubb');
    return $user;
  }
}
?>