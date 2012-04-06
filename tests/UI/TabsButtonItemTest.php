<?php

namespace Psc\UI;

use Psc\CMS\AbstractTabsContentItem2;

class TabsButtonItemTest extends \Psc\Code\Test\Base {
  
  protected $item;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\TabsButtonItem';
    parent::setUp();
    $this->item = $this->createTabsContentItem();
  }
  
  public function testConstruct() {
    $button = new TabsButtonItem($this->item);
    
    $html = $button->html();
    
    $button = $this->test->css('button', $html)
      ->count(1)
      ->hasText('Philipp S.')
      ->hasClass('psc-cms-ui-button');
      
    $data = $this->test->jQueryWidget('tabsContentItem',$html);
    
    $this->assertAttributeEquals(17, 'identifier', $data);
    $this->assertAttributeEquals($this->item->getTabsURL(), 'url', $data);
    $this->assertAttributeEquals('Philipp S.', 'label', $data);
    $this->assertAttributeEquals('Philipp Scheit', 'fullLabel', $data);
    $this->assertAttributeEquals(FALSE, 'drag', $data);
    
    $data = $this->test->jQueryWidget('button',$html);
  }
  
  public function createTabsContentItem() {
    $item = new AbstractTabsContentItem2('entities','person',17,'form','Philipp Scheit');
    $item->setTabsLabel('Philipp S.',AbstractTabsContentItem2::LABEL_BUTTON);
    return $item;
  }
}
?>