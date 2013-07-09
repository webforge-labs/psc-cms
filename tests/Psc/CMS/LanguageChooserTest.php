<?php

namespace Psc\CMS;

class LanguageChooserTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\LanguageChooser';
    parent::setUp();

    $this->chooser = new LanguageChooser(array('en', 'de'));
    $this->fallbackLanguage = 'en';
  }

  public function testCannotBeConstructedWithEmptyLanguages() {
    $this->setExpectedException('InvalidArgumentException');
    new LanguageChooser(array());
  }

  public function testUsesTheFirstLanguageAsFallback() {
    $chooser = new LanguageChooser(array('en', 'de'));

    $this->assertEquals('en', $chooser->getFallbackLanguage());
  }

  public function testUsesTheGivenFallbackLanguageAsFallback() {
    $chooser = new LanguageChooser(array('en', 'de'), 'de');

    $this->assertEquals('de', $chooser->getFallbackLanguage());
  }

  public function testIfNothingIsSetFallbackWillBeReturned() {
    $this->assertEquals($this->fallbackLanguage, $this->chooser->chooseLanguage());
  }

  /**
   * @dataProvider providePreferredLocales
   */
  public function testChoosesAnLanguageFromAnArrayOfPreferredLocales(Array $locales, $language) {
    $this->assertEquals(
      $language,

      $this->chooser
        ->fromPreferredLocales($locales)
        ->chooseLanguage()

    );
  }

  public static function providePreferredLocales() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test(array('de_DE', 'en_EN'), 'de');
    $test(array('en_GB', 'de_DE'), 'en');
    $test(array('en_US', 'de_DE'), 'en');
    $test(array(), 'en'); // fallback
    $test(array('fr_FR'), 'en'); // fallback
    $test(array('fr_FR', 'de_DE', 'en_US'), 'de');
    $test(array('fr', 'de', 'en'), 'de');

    return $tests;
  }

  public function testIfCombinedFirstFromWillMatch() {
    $chooser = new LanguageChooser(array('en', 'de', 'fr'));

    $this->assertEquals(
      'fr', 
      $chooser
        ->fromLanguage('fr')
        ->fromPreferredLocales(array('de_DE'))
        ->chooseLanguage(),
        'fromLanguage should match first'
    );
  }

  public function testIfCombinedFirstFromWillMatchPreferredFirst() {
    $chooser = new LanguageChooser(array('en', 'de', 'fr'));

    $this->assertEquals(
      'de', 
      $chooser
        ->fromPreferredLocales(array('de_DE'))
        ->fromLanguage('fr')
        ->chooseLanguage(),
      'preferred locales should match first'
    );
  }
}
