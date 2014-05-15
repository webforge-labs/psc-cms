<?php

namespace Psc\UI;

/**
 * @group class:Psc\UI\SplitPane
 */
class SplitPaneTest extends \Psc\Code\Test\HTMLTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\SplitPane';
    parent::setUp();
    
    $this->pane = new SplitPane(50, 'left-content', 'right-content');
  }
  
  public function testLeftAndRightContentIsSetThroughConstructor() {
    $this->assertEquals('right-content', $this->pane->getRightContent());
    $this->assertEquals('left-content', $this->pane->getLeftContent());
  }
  
  public function testLeftContentCanBeChangedBeforeInit() {
    $this->pane->setRightContent('other-right-content');
    $this->pane->setLeftContent('other-left-content');

    $this->assertEquals('other-right-content', $this->pane->getRightContent());
    $this->assertEquals('other-left-content', $this->pane->getLeftContent());
  }
  
  public function testHTMLLayoutAndContentCanBeChangedAfterInit() {
    $this->html = $this->pane->html();
    $this->pane->setRightContent('other-right-content');
    $this->pane->setLeftContent('other-left-content');
    
    $this->test->css('div.psc-cms-ui-splitpane')
      ->count(1)
      ->test('div.left')->count(1)->hasText('other-left-content')->end()
      ->test('div.right')->count(1)->hasText('other-right-content')->end()
    ;
  }
  
  public function testRightTagCanBeRetrievedAndModifiedAfterInitOnly() {
    $this->html = $this->pane->html();
    $this->pane->getRightTag()->addClass('new-class');
    $this->pane->getLeftTag()->addClass('new-class');
    
    $this->test
      ->css('div.psc-cms-ui-splitpane')->count(1)
      ->css('div.right.new-class')->count(1)->end()
      ->css('div.left.new-class')->count(1)->end();
  }
}
?>