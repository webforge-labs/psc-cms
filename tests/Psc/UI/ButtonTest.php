<?php

namespace Psc\UI;

/**
 * @group class:Psc\UI\Button
 */
class ButtonTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $button;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\Button';
    parent::setUp();
    $this->button = new Button('a nice Button');
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Psc\UI\ButtonInterface', $this->button);
    
    $this->button->setLeftIcon('gear');
    $this->assertEquals('gear', $this->button->getLeftIcon());
    
    $this->html = $this->button->html();
    
    $this->test
      ->css('button.psc-cms-ui-button')
        ->count(1)
        ->hasAttribute('data-widget','button')
        ->hasAttribute('data-widget-options', '{"icons":{"primary":"ui-icon-gear"}}'); // @TODO contribute matchesJson() to PHPUnit
  }
  
  public function testSetHintIsAppendedToButtonWhenNotinit() {
    $this->button->setHint($desc = 'description for button');
    
    $this->html = $this->button->html();
    
    $this->test->css('.hint')->count(1)->containsText($desc);
  }

  public function testSetHintIsAppendedToButtonWhenAlreadyInit() {
    $this->button->html();
    $this->button->setHint($desc = 'description for button');
    
    $this->html = $this->button->html();
    $this->test->css('.hint')->count(1)->containsText($desc);
  }
  
  public function testDisabledWithMessageParameterSetsHint() {
    $this->button->disable($reason = 'this is the reason why the button is disabled');
    
    $this->html = $this->button->html();
    $this->test->css('.hint')->count(1)->containsText($reason);
  }
  
  public function testLabelSynchronizesAfterHTMLIsCalled() {
    
    $this->button->html();
    $this->button->setLabel($label = 'changed label after html');
    
    $this->html = $this->button->html();
    $this->test->css('button.psc-cms-ui-button')->count(1)->containsText($label);
  }
}
?>