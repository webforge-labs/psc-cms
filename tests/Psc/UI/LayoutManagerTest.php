<?php

namespace Psc\UI;

use Psc\Entities\ContentStream\ContentStream;

/**
 * @group class:Psc\UI\LayoutManager
 */
class LayoutManagerTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $layoutManager;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\LayoutManager';
    parent::setUp();
    $this->layoutManager = new LayoutManager(new \Psc\UI\UploadService('/', '/'));

    $this->pageContentStream = ContentSTream::create('de', 'page-content', 'default');
  }
  
  public function testHTML_butThisIsNowInJS() {
    $this->html = $this->layoutManager->html();
    
    $this->assertNotEmpty($this->html);
    $this->assertContains('\Psc\serializable', $this->html->getAttribute('class'), 'html should have class serializable because the widget is linked here');
  }

  public function testControlsWillBeInitForPageContentStream() {
    $this->html = $this->layoutManager->html();
    $this->layoutManager->initControlsFor($this->pageContentStream);

    $this->assertGreaterThan(4, count($this->layoutManager->getControls()));

    $this->test->js($this->layoutManager)
      ->constructsJoose('Psc.UI.LayoutManager')
      ->hasParam('controls', $this->isType('array'))
      ->hasParam('widget')
      ->hasParam('container')
      ->hasParam('serializedWidgets', $this->isType('array'))
    ;
  }

  public function testHackFlatNavigationInjection() {
    $this->html = $this->layoutManager->html();
    $this->layoutManager->hackFlatNavigationInjection($flat = array((object) array('fake'=>'flat')));

    $this->test->js($this->layoutManager)
      ->constructsJoose('Psc.UI.LayoutManager')
      ->hasParam('injectNavigationFlat', $this->equalTo($flat));
  }
}
