<?php

namespace Psc\CMS\Item;

class TabButtonableValueObjectTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Item\\TabButtonableValueObject';
    parent::setUp();
    
    $this->defaultObj = $this->obj = new TabButtonableValueObject();
    $this->item = $this->getEntityMeta('Psc\Doctrine\TestEntities\Article')->getAdapter(current($this->loadTestEntities('articles')));
  }
  
  public function testPreConditionForItem() {
    $this->assertInstanceOf('Psc\CMS\Item\TabButtonable', $this->item);
  }
  
  public function testVOImplementsTabButtonable() {
    $this->assertInstanceOf('Psc\CMS\Item\TabButtonable', $this->obj);
  }
  
  public function testValueObjectCanBeCreatedFromOtherTabButtonable() {
    $this->assertInstanceOf($this->chainClass, TabButtonableValueObject::copyFrom($this->item));
  }

  /**
   * @dataProvider provideDefaultMethods
   */
  public function testValueObjectCanBeCreatedFromTabButtonable_whichCopiesAllValues($method) {
    $object = TabButtonableValueObject::copyFrom($this->item);
    
    $this->assertSame(
      $this->item->$method(),
      $object->$method()
    );
  }
  
  public function testButtonModeIsClickAndDragPerDefault() {
    $this->assertEquals(
      TabButtonable::CLICK | TabButtonable::DRAG,
      $this->defaultObj->getButtonMode(),
      'button mode should be click | drag per default'
    );
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
  
}
?>