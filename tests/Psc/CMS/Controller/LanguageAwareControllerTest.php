<?php

namespace Psc\CMS\Controller;

/**
 * @group class:Psc\CMS\Controller\LanguageAwareController
 */
class LanguageAwareControllerTest extends \Psc\Code\Test\Base {
  
  protected $languageAwareController;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\LanguageAwareController';
    parent::setUp();
    $this->ctrl = new LanguageAwareController(array('de','en'), 'en');
  }
  
  public function testControllerGetterHasTheConstructorLanguageSet() {
    $this->assertEquals('en', $this->ctrl->getLanguage());
  }
  
  /**
   * @dataProvider validLanguages
   */
  public function testLanguageCanBeSetToLanguagesFromConstructor($lang) {
    $this->ctrl->setLanguage($lang);
    $this->assertEquals($lang, $this->ctrl->getLanguage());
  }
  
  public function testLanguageCannotBeSetToLanguagesNotInConstructor() {
    $this->setExpectedException('Psc\Exception');
    $this->ctrl->setLanguage('fr');
  }
  
  public static function validLanguages() {
    return array(
      array('de'),
      array('en')
    );
  }
}
?>