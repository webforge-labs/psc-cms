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
    $this->layoutManager = new LayoutManager('the label', new \Psc\UI\UploadService('/', '/'));
  }
  
  public function testHTML_butThisIsNowInJS() {
    $this->html = $this->layoutManager->html();
    
    $this->assertNotEmpty($this->html);
    $this->assertContains('\Psc\serializable', $this->html->getAttribute('class'), 'html should have class serializable because the widget is linked here');
  }
}
