<?php

namespace Psc\CMS\Roles;

class AbstractContainerTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Roles\\AbstractContainer';
    parent::setUp();

    $this->dc = $this->doublesManager->createDoctrinePackageMock();
    $this->languages = array('de', 'en');
    $this->language = 'de';

    $this->container = new \Psc\Test\CMS\Container('Psc\Test\Controllers', $this->dc, $this->languages, $this->language);
  }

  public function testPre() {
    $this->assertInstanceOf($this->chainClass, $this->container);
  }

  public function testReturnsTheCurrentPackage() {
    $this->assertInstanceOf('Webforge\Framework\Package\Package', $this->container->getPackage());
  }

  public function testReturnsACurrentProjectPackage() {
    $this->assertInstanceOf('Webforge\Framework\Package\ProjectPackage', $package = $this->container->getProjectPackage());

    // ATTENTION: $this->languages has other content that $package->getLanguages() 
    // because package reads local config and ProjectPackage cannot be injected
  }

  public function testReturnsATranslationContainer() {
    $this->assertInstanceOf('Psc\CMS\Translation\Container', $this->container->getTranslationContainer());
  }

  public function testInjectsATranslationContainer() {
    $container = $this->getMockBuilder('Psc\CMS\Translation\Container')->disableOriginalConstructor()->getMock();
    $this->container->setTranslationContainer($container);

    $this->assertSame($this->container->getTranslationContainer(), $container);
  }

  public function testReturnsAnWebforgeTranslatorWithTheRightLocales() {
    $this->assertInstanceOf('Webforge\Translation\Translator', $translator = $this->container->getTranslator());
    $package = $this->container->getProjectPackage();

    $this->assertEquals(
      $package->getDefaultLanguage(),
      $translator->getLocale()
    );
  }

  public function testTranslatorReadsTranslationsFormTheProjectPackageConfig() {
    $this->container->getProjectPackage()->getConfiguration()->set(array('languages'), $this->languages);

    $this->container->getProjectPackage()->getConfiguration()->set(array('translations'), Array(
      'de'=>Array(
        'hello'=>'Guten Tag'
      ),
      'en'=>Array(
        'hello'=>'Good Afternoon'
      )
    ));

    $this->assertEquals(
      'Guten Tag',
      $this->container->getTranslator()->trans('hello')
    );

    $this->container->setLanguage('en');

    $this->assertEquals('en', $this->container->getTranslator()->getLocale());

    $this->assertEquals(
      'Good Afternoon',
      $this->container->getTranslator()->trans('hello')
    );
  }

  public function testReturnsASystemContainer() {
    $this->assertInstanceOf('Webforge\Common\System\Container', $this->container->getSystemContainer());
  }
}
