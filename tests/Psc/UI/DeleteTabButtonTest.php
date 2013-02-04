<?php

namespace Psc\UI;

use Psc\CMS\Item\Buttonable;
use Psc\CMS\Item\TabButtonable;
use Closure;

/**
 * @group class:Psc\UI\DeleteTabButton
 */
class DeleteTabButtonTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $tabButton;
  protected $bridgedItem;
  protected $jooseBridge;
  protected $item;
  protected $bridgedClass;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\DeleteTabButton';
    $this->bridgedClass = 'Psc\CMS\Item\DeleteButtonable';
    parent::setUp();
    $this->item = $this->getEntityMeta('Psc\Doctrine\TestEntities\Article')
                      ->getAdapter(current($this->loadTestEntities('articles')))
                      ->getDeleteButtonable();
    $this->jooseBridge = $this->getMock('Psc\CMS\Item\JooseBridge', array('setItem','link','autoLoad','html'), array($this->item));
  }
  
  public function testPreItemHasDeleteRequestMeta() {
    $this->assertInstanceOf('Psc\CMS\RequestMetaInterface', $this->item->getDeleteRequestMeta());
  }

  public function testBridgedButtonableIsDeletable() {
    $this->prepareBridgedButton();
    
    $deleteButtonable = $this->bridgeItem();
    
    $this->assertInstanceOf('Psc\CMS\Item\DeleteButtonable', $deleteButtonable);
  }
  
  public function provideDefaultMethods() {
    return Array(
      array('getButtonLabel'),
      array('getFullButtonLabel'),
      array('getButtonRightIcon'),
      array('getButtonLeftIcon'),
      array('getButtonMode'),
      array('getDeleteRequestMeta')
    );
  }
  
  public function testDeleteRequestMetaIsTheSameAsForItemButtonable() {
    $this->tabButton = new DeleteTabButton($this->item, $this->jooseBridge);
    
    $this->assertSame(
      $this->item->getDeleteRequestMeta(),
      $this->tabButton->getDeleteRequestMeta()
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
  
  //public function testDeleteRequestMetaCanBeOverriden() {
  //  $this->tabButton = new DeleteTabButton($this->item, $this->jooseBridge);
  //  $this->tabButton->setTabRequestMeta(
  //    $rm = $this->getMockForAbstractClass('Psc\CMS\RequestMetaInterface')
  //  );
  //  
  //  $this->assertSame(
  //    $this->tabButton->getTabRequestMeta(),
  //    $rm
  //  );
  //}
  
  /**
   * @return Psc\CMS\Item\TabButtonable
   */
  protected function expectBridgedButtonable(Closure $hook = NULL) {
    $this->prepareBridge();
    
    $this->tabButton = new DeleteTabButton($this->item, $this->jooseBridge);
    
    return $this->bridgeItem();
  }
  
  protected function prepareBridgedButton() {
    $this->prepareBridge();
    $this->tabButton = new DeleteTabButton($this->item, $this->jooseBridge);
  }
  
  protected function prepareBridge() {
    $this->expectBridgeGetsItem();
    $this->expectJooseBridgeDefaults();
  }
  
  protected function bridgeItem() {
    $this->html = $this->tabButton->html();
    $this->assertInstanceOf($this->bridgedClass, $this->bridgedItem, 'Button ruft nicht jooseBridge->setItem() auf');
    return $this->bridgedItem;
  }
  
  protected function expectBridgeGetsItem() {
    $this->jooseBridge->expects($this->once())->method('setItem')
                      ->with($this->isInstanceOf($this->bridgedClass))
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
?>