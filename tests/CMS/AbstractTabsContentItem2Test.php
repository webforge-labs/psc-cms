<?php

namespace Psc\CMS;

class AbstractTabsContentItem2Test extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\AbstractTabsContentItem2';
    parent::setUp();
  }
  
  public function testConstruct() {
    $item = new AbstractTabsContentItem2('entities','person',17,'form','Philipp Scheit');
    
    $this->assertEquals(array('entities','person',17,'form'), $item->getTabsId());
    $this->assertEquals('entities',$item->getTabsServiceName());
    $this->assertEquals('person',$item->getTabsResourceName());
    $this->assertEquals(17,$item->getTabsIdentifier());
    $this->assertEquals('form',$item->getTabsAction());
    $this->assertEquals(array(),$item->getTabsData());
    $this->assertEquals('Philipp Scheit',$item->getTabsLabel());
  }
  
  public function testGetURL() {
    $item = new AbstractTabsContentItem2('entities','person',17,'form','Philipp Scheit');
    
    $this->assertEquals('/entities/person/17/form/?isNew=true',$item->getTabsURL(array('isNew'=>'true')));
  }
  
  public function testSetters() {
    $item = new AbstractTabsContentItem2('entities','person',17,'form', 'Philipp Scheit', array('product'=>FALSE));
    $this->assertEquals(array('product'=>FALSE),$item->getTabsData());
    
    $this->assertChainable($item->setTabsServiceName('_entities'));
    $this->assertChainable($item->setTabsResourceName('_person'));
    $this->assertChainable($item->setTabsIdentifier(7));
    $this->assertChainable($item->setTabsAction('_form'));
    $this->assertChainable($item->setTabsData(array('product'=>TRUE)));
    $this->assertChainable($item->setTabslabel('Philipp S', 'default'));
    $this->assertChainable($item->setTabslabel('PS', 'button'));
    
    $this->assertEquals(array('_entities','_person',7,'_form'), $item->getTabsId());
    $this->assertEquals('_entities',$item->getTabsServiceName());
    $this->assertEquals('_person',$item->getTabsResourceName());
    $this->assertEquals(7,$item->getTabsIdentifier());
    $this->assertEquals('_form',$item->getTabsAction());
    $this->assertEquals(array('product'=>TRUE),$item->getTabsData());
    $this->assertEquals('Philipp S',$item->getTabsLabel('default'));
    $this->assertEquals('PS',$item->getTabsLabel('button'));
  }
}
?>