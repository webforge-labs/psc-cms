<?php

namespace Psc\CMS\Controller;

class FactoryTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Controller\\Factory';
    parent::setUp();

    $this->languages = array('de', 'jp');
    $this->language = 'jp';

    $this->setUpDependencyProvider();
    $this->factory = new Factory('Psc\Test\Controllers', $this->depsProvider);
    
    $this->getMockForAbstractClass('Psc\CMS\Controller\SimpleContainerController', array(), 'WO_NAMESPACE_SimpleContainerController', FALSE);
    $this->factory->setControllerFQN('LanguageAware', 'Psc\CMS\Controller\LanguageAwareController');
    $this->factory->setControllerFQN('AbstractEntity', 'Psc\Test\Controllers\NavigationNodeController');
    $this->factory->setControllerFQN('SCC', 'WO_NAMESPACE_SimpleContainerController');
  }

  public function testConstructsControllerFromNameInDefaultNamespace() {
    $this->assertControllerFQN(
      $fqn = 'Psc\Test\Controllers\NavigationNodeController',
      $this->factory->getController('NavigationNode')
    );
  }

  public function testNamesCanBeInjected() {
    $this->assertControllerFQN(
      'Psc\CMS\Controller\LanguageAwareController',
      $this->factory->getController('LanguageAware')
    );
  }

  public function testAbstractEntityControllerExtendingControllerGetsDCPackageInjectedFromOurProvider() {
    $this->depsProviderExpectsDCPackageGet();

    $controller = $this->factory->getController('AbstractEntity');

    $this->assertInstanceOf('Psc\CMS\Controller\AbstractEntityController', $controller);
    $this->assertSame($this->dc, $controller->getDoctrinePackage(), 'dcPackage is not injected into AbstractEntityController');
  }

  public function testSimpleContainerControllerGetsContainerInserted() {
    $this->depsProviderExpectsDCPackageGet();
    $this->depsProviderExpectsSimpleContainerGet();

    $controller = $this->factory->getController('SCC');
    $this->assertInstanceOf('Psc\CMS\Controller\SimpleContainerController', $controller);

    $this->assertSame($this->container, $controller->getContainer(), 'container should be injected into SimpleContainerController');
  }

  public function testLanguageAwareControllerWillBeInjectedWithLanguages() {
    $this->depsProviderExpectsLanguagesGet();

    $controller = $this->factory->getController('LanguageAware');
    $this->assertInstanceOf('Psc\CMS\Controller\LanguageAware', $controller);

    $this->assertSame($this->language, $controller->getLanguage(), 'language should be injected to languageAware');
    $this->assertSame($this->languages, $controller->getLanguages(), 'languages should be injeected to languageware');
  }

  protected function setUpDependencyProvider() {
    $this->dc = $this->doublesManager->createDoctrinePackageMock();

    $this->depsProvider = $this->getMockForAbstractClass('Psc\CMS\Roles\AbstractControllerContainer', array($this->dc, $this->languages, $this->language));
    $this->container = $this->depsProvider->getSimpleContainer();

    $this->assertInstanceOf('Psc\CMS\Roles\ControllerDependenciesProvider', $this->depsProvider);

    return $this->depsProvider;
  }

  protected function assertControllerFQN($fqn, $controller, $msg = '') {
    $this->assertInstanceOf($fqn, $controller, $msg);
  }

  protected function depsProviderExpectsDCPackageGet() {
    $this->depsProviderExpectsGet('dc', $this->dc);
  }

  protected function depsProviderExpectsSimpleContainerGet() {
    $this->depsProviderExpectsGet('container', $this->container);
  }

  protected function depsProviderExpectsLanguagesGet() {
    $this->depsProviderExpectsGet('languages', $this->languages);
    $this->depsProviderExpectsGet('language', $this->language);
  }

  protected function depsProviderExpectsGet($key, $value) {
    // we use the real abstract class for most dependencies and do not need to mock much

  }
}
