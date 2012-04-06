<?php

namespace Psc\UI;

class TabsContentItemAdapterTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\TabsContentItemAdapter';
    parent::setUp();
    
    $this->ctiAdapter = new TabsContentItemAdapter(new \Psc\CMS\AbstractTabsContentItem2('cms','tpl','test','layout.manager', 'LayoutManagerTest'));
  }
  
  public function testGetLink() {
    $link = $this->ctiAdapter->getLink();
    $this->assertInstanceOf('Psc\Data\Type\Interfaces\Link', $link);
    
    $this->test->css('a',$link->html())
      ->count(1)
      ->hasAttribute('href')
      ->hasText('LayoutManagerTest')
      // siehe auch Psc.UI.Main (js) in attachHandlers()
      ->hasClass('psc-cms-ui-tabs-item')
    ;
  }

  public function testGetButton() {
    $button = $this->ctiAdapter->getButton();
    $this->assertInstanceOf('Psc\UI\Button', $button); // @todo psc\data\type\interfaces\button wäre cool
    
    // alles weitere überlassen wir ja den ui button
    $this->test->css('button',$button->html())
      ->hasClass('psc-cms-ui-button')
      ->count(1);
  }
}
?>