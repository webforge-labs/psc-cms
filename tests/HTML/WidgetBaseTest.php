<?php

namespace Psc\HTML;

/**
 * @group class:Psc\HTML\WidgetBase
 */
class WidgetBaseTest extends \Psc\Code\Test\HTMLTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\HTML\WidgetBase';
    parent::setUp();
  }
  
  public function testConstruct() {
    $widgetBase = new Accordion('accordion',array('open'=>1));
    
    $this->assertInstanceOf('Psc\HTML\HTMLInterface', $widgetBase);
    
    $this->html = $widgetBase->html();
    
    $this->test->css('div')
      ->count(1)
    ;
    
    $this->test->css('script[type="text/javascript"]')
        ->atLeast(1)
        ->containsText('.accordion(')
    ;
  }
}

class Accordion extends WidgetBase {
  
  protected function doInit() {
    $this->html = \Psc\HTML\HTML::tag('div');
    parent::doInit();
  }
}
?>