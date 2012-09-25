<?php

namespace Psc\Form;

/**
 * @group class:Psc\Form\I18nValidatorRule
 */
class I18nValidatorRuleTest extends \Psc\Form\ValidatorRuleTestCase {
  
  protected $rule, $languages;
  
  public function setUp() {
    $this->chainClass = 'Psc\Form\I18nWrapperValidatorRule';
    $this->languages = array('fr','de');
    parent::setUp();
  }
  
  protected function createRule() {
    return new I18nWrapperValidatorRule(new NesValidatorRule(), $this->languages);
  }
  
  // LanguageArray
  public static function la($frValue, $deValue) {
    return array('fr'=>$frValue, 'de'=>$deValue);
  }
  
  public function provideValidData() {
    $tests = array();
    
    $tests[] = array(self::la('fr is not empty','de is not empty'));
    
    return $tests;
  }
  
  public function provideInvalidData() {
    $tests = array();
    
    $tests[] = array(NULL);
    
    return $tests;
  }
    
    
  public function provideEmptyData() {
    $tests = array();
    
    // diese Providers gehen mir sowas von aufn Sack!
    $tests[] = array(self::la(NULL,'de is not empty'));
    $tests[] = array(self::la('fr is not empty',NULL));
    $tests[] = array(array('de'=>'not empty'));
    $tests[] = array(array('fr'=>'not empty'));
    
    return $tests;
  }
}
?>