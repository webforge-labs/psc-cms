<?php

namespace Psc\UI;

/**
 * @group class:Psc\UI\SplitPane
 */
class SplitPaneTest extends \Psc\Code\Test\HTMLTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\SplitPane';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $splitPane = new SplitPane(50, 'left', 'right');
    $this->assertEquals('left', $splitPane->getLeftContent());
    $splitPane->setLeftContent('mal was linkes');
    
    $this->html = $splitPane->html();
    
    $this->test->css('div.psc-cms-ui-splitpane')
      ->count(1)
      ->test('div.left')->count(1)->hasText('mal was linkes')->end()
      ->test('div.right')->count(1)->hasText('right')->end()
    ;
  }
}
?>