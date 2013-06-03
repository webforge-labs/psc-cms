<?php

namespace Psc\UI;

use Psc\CMS\Item\Buttonable;
use Psc\CMS\Item\TabButtonable;
use Closure;

/**
 * @group class:Psc\UI\TabButton
 */
class TabButtonTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $tabButton;
  protected $bridgedItem;
  protected $jooseBridge;
  protected $item;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\TabButton';
    parent::setUp();
    $this->item = $this->getEntityMeta('Psc\Doctrine\TestEntities\Article')->getAdapter(current($this->loadTestEntities('articles')));
    $this->jooseBridge = $this->getMock('Psc\CMS\Item\JooseBridge', array('setItem','link','autoLoad','html'), array($this->item));
  }
  
  public function testButtonImplementsButtonInterface() {
    $this->tabButton = new TabButton($this->item, $this->jooseBridge);
    $this->assertInstanceof('Psc\UI\ButtonInterface', $this->tabButton);
  }
  
  public function testButtonBridgesAnItemForJoose() {
    $this->expectJooseBridgeDefaults();
    
    $this->tabButton = new TabButton($this->item, $this->jooseBridge);
    
    $this->html = $this->tabButton->html();
    $this->assertEquals('<a>the html</a>', $this->html);
  }  
  
  public function provideDefaultMethods() {
    return Array(
      array('getButtonLabel'),
      array('getFullButtonLabel'),
      array('getButtonRightIcon'),
      array('getButtonLeftIcon'),
      array('getButtonMode'),
      array('getTabRequestMeta'),
      array('getTabLabel')
    );
  }
  
  /**
   * @dataProvider provideDefaultMethods
   */
  public function testBridgedButtonableHasSameValuesAsItemByDefault($method) {
    $buttonable = $this->expectBridgedButtonable();
    
    $this->assertSame(
      $itemValue = $this->item->$method(),
      $buttonable->$method(),
      'the bridged item does not return the value from item by default for '.$method.' value in item: '.$itemValue
    );
  }
  
  public function testBridgedButtonableHasLabelWhenOverriden() {
    $this->prepareBridgedButton();
    
    $this->tabButton->setLabel('some other label');
    
    $buttonable = $this->bridgeItem();
    
    $this->assertEquals(
      'some other label',
      $buttonable->getButtonLabel()
    );
  }

  public function testButtonCanSetBridgedItemToClick() {
    $this->prepareBridgedButton();
    
    $this->tabButton->onlyClickable();
    
    $buttonable = $this->bridgeItem();
    
    $this->assertEquals(
      TabButtonable::CLICK,
      $buttonable->getButtonMode()
    );
  }

  public function testButtonCanSetBridgedItemToDrag() {
    $this->prepareBridgedButton();
    
    $this->tabButton->onlyDraggable();
    
    $buttonable = $this->bridgeItem();
    
    $this->assertEquals(
      TabButtonable::DRAG,
      $buttonable->getButtonMode()
    );
  }
  
  public function testButtonCanSetBridgedItemToDragAndClick() {
    $this->prepareBridgedButton();
    
    $this->tabButton->clickableAndDraggable();
    
    $buttonable = $this->bridgeItem();
    
    $this->assertEquals(
      TabButtonable::DRAG | TabButtonable::CLICK,
      $buttonable->getButtonMode()
    );
  }
  
  public function testTabRequestMetaIsTheSameAsForItemButtonable() {
    $this->tabButton = new TabButton($this->item, $this->jooseBridge);
    
    $this->assertSame(
      $this->item->getTabRequestMeta(),
      $this->tabButton->getTabRequestMeta()
    );
  }
  
  public function testTabRequestMetaCanBeOverriden() {
    $this->tabButton = new TabButton($this->item, $this->jooseBridge);
    $this->tabButton->setTabRequestMeta(
      $rm = $this->getMockForAbstractClass('Psc\CMS\RequestMetaInterface')
    );
    
    $this->assertSame(
      $this->tabButton->getTabRequestMeta(),
      $rm
    );
  }
  
  /**
   * @return Psc\CMS\Item\TabButtonable
   */
  protected function expectBridgedButtonable(Closure $hook = NULL) {
    $this->prepareBridge();
    
    $this->tabButton = new TabButton($this->item, $this->jooseBridge);
    
    return $this->bridgeItem();
  }
  
  protected function prepareBridgedButton() {
    $this->prepareBridge();
    $this->tabButton = new TabButton($this->item, $this->jooseBridge);
  }
  
  protected function prepareBridge() {
    $this->expectBridgeGetsItem();
    $this->expectJooseBridgeDefaults();
  }
  
  protected function bridgeItem() {
    $this->html = $this->tabButton->html();
    $this->assertInstanceOf('Psc\CMS\Item\TabButtonable', $this->bridgedItem, 'Button ruft nicht jooseBridge->setItem() auf');
    return $this->bridgedItem;
  }
  
  protected function expectBridgeGetsItem() {
    $this->jooseBridge->expects($this->once())->method('setItem')
                      ->with($this->isInstanceOf('Psc\CMS\Item\TabButtonable'))
                      ->will($this->returnCallback(array($this, 'jooseBridge_setItemMock')));
  }
  
  public function jooseBridge_setItemMock($item) {
    $this->bridgedItem = $item;
    return $this->jooseBridge;
  }
  
  protected function expectJooseBridgeDefaults() {
    $this->jooseBridge->expects($this->once())->method('link')
                      ->with($this->isInstanceOf('Psc\HTML\Tag'))->will($this->returnSelf());

    $this->jooseBridge->expects($this->once())->method('html')
                      ->will($this->returnValue(\Psc\HTML\HTML::tag('a', 'the html')));
  }
}
