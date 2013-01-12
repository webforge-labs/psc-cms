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
    
    //$this->assertContains('.button({', (string) $this->html);
  }
  
  public function testSetHintIsAppendedToButtonWhenNotinit() {
    $this->markTestIncomplete('@tODO');
  }

  public function testSetHintIsAppendedToButtonWhenAlreadyInit() {
    $this->markTestIncomplete('@TODO');
  }
  
  public function testDisabledWithMessageParameterSetsHint() {
    $this->markTestIncomplete('@TODO');
  }
  
  public function testLabelSynchronizesAfterHTMLIsCalled() {
    $this->markTestIncomplete('@TODO');
  }
}
?>