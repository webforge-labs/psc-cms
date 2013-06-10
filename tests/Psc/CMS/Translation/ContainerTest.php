<?php

namespace Psc\CMS\Translation;

class ContainerTest extends \Psc\Test\DatabaseTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Translation\\Container';
    parent::setUp();

    $this->translationContainer = $this->getContainer()->getTranslationContainer();
    $this->translationContainer->setLocale('en');
  }

  public function testTranslationContainerHasENTranslationsFromCMSTranslationsFiles() {
    $this->translationContainer->loadTranslationsFromPackage(
      $this->getContainer()->getPackage()
    );

    $this->translationContainer->getTranslator()->trans('welcome.tabTitle');

    $enCatalogue = require $this->getPackageDir('resources/translations/')->getFile('cms.en.php');

    foreach ($enCatalogue as $key => $enValue) {
      if (is_string($enValue)) {
        $this->assertEquals(
          $enValue,
          $this->translationContainer->getTranslator()->trans($key, array(), 'cms'),
          sprintf("Translation key '%s' from resources/translations/cms.en.php is not loaded", $key)
        );
      }
    }
  }
}
