<?php

namespace Psc\UI;

/**
 * @group class:Psc\UI\LayoutManager
 */
class LayoutManagerTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $layoutManager;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\LayoutManager';
    parent::setUp();
    $this->layoutManager = new LayoutManager('the label');
  }
  
  public function testHTML() {
    $this->html = $this->layoutManager->html();
    
    $this->test->css('.psc-cms-ui-splitpane.psc-cms-ui-serializable')->count(1);
    $this->test->css('.psc-cms-ui-splitpane > div.left > fieldset.psc-cms-ui-group')->count(1);
    $this->test->css('.psc-cms-ui-splitpane > div.right')->count(1)
      ->test('> .psc-cms-ui-accordion')->count(1)
        ->test('button')->atLeast(3)->end()
      ->end()
      
      ->test('> .magic-helper textarea')->count(1)->end()
    ;
  }
}
?>