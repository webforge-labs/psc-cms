<?php

namespace Psc\System;

/**
 * @group class:Psc\System\LocaleHelper
 */
class LocaleHelperTest extends \Psc\Code\Test\Base {
  
  protected $localeHelper;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\LocaleHelper';
    parent::setUp();
  }
  
  /**
   * @dataProvider toLanguages
   */
  public function testToLanguage($locale, $lang) {
    $this->assertEquals($lang, LocaleHelper::toLanguage($locale));
  }
  
  public static function toLanguages() {
    $tests = array();
    
    $tests[] = array('de_DE', 'de');
    $tests[] = array('en_EN', 'en');
    $tests[] = array('fr_FR', 'fr');
    $tests[] = array('fr', 'fr');
    $tests[] = array('en', 'en');
    
    return $tests;
  }
  
}
?>