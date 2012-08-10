<?php

namespace Psc\CMS\Item;

/**
 * @group class:Psc\CMS\Item\MetaAdapter
 */
class MetaAdapterTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $adapter;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Item\MetaAdapter';
    parent::setUp();
    
    $this->adapter = new MetaAdapter($this->getEntityMeta('Psc\Doctrine\TestEntities\Article'));
  }
  
  public function testGetNewTabButtonIsButtonAndConstructsJoose() {
    $button = $this->adapter->getNewTabButton();
    $this->assertInstanceOf('Psc\UI\ButtonInterface', $button);
    
    $this->assertNotEmpty($button->getLabel());
    
    $this->html = $button->html();
    
    $this->test->css('button.psc-cms-ui-button')->count(1);
    $this->test->js($button)
      ->constructsJoose('Psc.CMS.FastItem')
      ->hasParam('tab')
      ->hasParam('button')
    ;
  }

  public function testGetRightContentLinkable() {
    $this->assertInstanceOf('Psc\CMS\Item\RightContentLinkable', $this->adapter->getRCLinkable());
  }
}
?>