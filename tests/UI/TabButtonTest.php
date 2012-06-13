<?php

namespace Psc\UI;

/**
 * @group class:Psc\UI\TabButton
 */
class TabButtonTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $tabButton;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\TabButton';
    parent::setUp();
    $this->item = $this->getEntityMeta('Psc\Doctrine\TestEntities\Article')->getAdapter(current($this->loadTestEntities('articles')));
    $this->jooseBridge = $this->getMock('Psc\CMS\Item\JooseBridge', array('link','autoLoad'), array($this->item));
    $this->tabButton = new TabButton($this->item, $this->jooseBridge);
  }
  
  public function testAcceptance() {
    $this->assertInstanceof('Psc\UI\ButtonInterface', $this->tabButton);
    $this->assertEquals($this->item->getButtonLabel(),$this->tabButton->getLabel());
    
    $this->jooseBridge->expects($this->once())->method('link')
                      ->with($this->isInstanceOf('Psc\HTML\Tag'))->will($this->returnSelf());
    
    $this->html = $this->tabButton->html();
  }
}
?>