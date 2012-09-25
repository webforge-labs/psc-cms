<?php

namespace Psc\UI\Component;

/**
 * @group class:Psc\UI\Component\I18nWrapper
 */
class I18nWrapperTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $i18nWrapper;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\Component\I18nWrapper';
    parent::setUp();
    //$this->i18nWrapper = new I18nWrapper();
  }
  
  public function testAcceptance() {
    $component = new I18nWrapper();
    $component->dpi(new \Psc\Data\Type\StringType(), array('de','fr'));
    
    $component->setFormName('title');
    $component->setFormValue(array('de'=>'de-title', 'fr'=>'fr-title'));
    $component->setFormLabel('Titel');
    
    $component->init();
    
    $this->html = $component->html();
    
    $this->test->css('div.component-wrapper')
      ->css('input[type="text"]')->count(2)->end()
      ->css('input[name="title[de]"]')->count(1)->end()
      ->css('input[name="title[fr]"]')->count(1)->end()
    ;
  }
}
?>