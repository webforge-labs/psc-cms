<?php

namespace Psc\CMS\Controller;

class FactoryTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Controller\\Factory';
    parent::setUp();

/*
    $fqns = array(
      'Page'=>'Psc\Entities\Page',
      'ContentStream'=>'Psc\Entities\ContentStream\ContentStream',
      'NavigationNode'=>'Psc\Entities\NavigationNode'
    );
    $this->fqnSolver = $this->getMockForAbstractClass('Psc\CMS\Roles\FQNSolver');
    $this->fqnSolver->expects($this->any())->method('getRoleFQN')->will($this->returnCallback(function ($name) use ($fqns) {
      return $fqns[$name];
    }));
*/
    $this->factory = new Factory('Psc\Test\Controllers');
    
    $this->getMockForAbstractClass('Psc\CMS\Controller\LanguageAware', array(), 'WO_NAMESPACE_SomeLanguageAwareController');
    $this->factory->setControllerFQN('LanguageAware', 'WO_NAMESPACE_SomeLanguageAwareController');
  }

  public function testConstructsControllerFromNameInDefaultNamespace() {
    $this->assertControllerFQN(
      $fqn = 'Psc\Test\Controllers\NavigationNodeController',
      $this->factory->getController('NavigationNode')
    );
  }

  public function testConstructsControllerFromEntityFQNInDefaultNamespace() {
    $this->assertControllerFQN(
      $fqn = 'Psc\Test\Controllers\NavigationNodeController',
      $this->factory->getControllerForEntity('Psc\Entities\NavigationNode')
    );
  }

  public function testNamesCanBeInjected() {
    $this->assertControllerFQN(
      'WO_NAMESPACE_SomeLanguageAwareController',
      $this->factory->getController('LanguageAware')
    );
  }

  public function testLanguageAwareControllerGetsLanguageAndLanguagesSet() {
    
  }

  protected function assertControllerFQN($fqn, $controller, $msg = '') {
    $this->assertInstanceOf($fqn, $controller, $msg);
  }
}
?>