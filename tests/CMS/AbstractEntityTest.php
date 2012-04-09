<?php

namespace Psc\CMS;

use Psc\CMS\AbstractEntity;
use Psc\Code\Event\Event;

class AbstractEntityTest extends \Psc\Code\Test\Base {
  
  protected $defaultEntity;
  
  public function setUp() {
    parent::setUp();
    $this->defaultEntity = $this->createInstance(1);
    $this->chainClass = 'Psc\CMS\MyCMSEntity';
  }
  
  public function testComponentsCreaterDynamicListening() {
    $entity = $this->getMock('Psc\CMS\MyCMSEntity', array('onBirthdayComponentCreated'), array(7));
    $component = $this->getMock('Psc\UI\Component\Base', array('getFormName','getInnerHTML'));
    $creater = $this->getMock('Psc\CMS\EntityFormPanel', array(), array(),'', FALSE);
    $event = new Event(ComponentsCreater::EVENT_COMPONENT_CREATED, $this);
    $event->setData(array('name'=>'birthday'));
    
    $entity->expects($this->once())->method('onBirthdayComponentCreated')
           ->with($this->equalTo($component), // warum geht hier identicalTo nicht?
                  $this->equalTo($creater),
                  $this->equalTo($event)
                 );
    
    $entity->onComponentCreated($component, $creater, $event);
  }
  
  public function testExport() {
    $entityName = 'Psc\Doctrine\TestEntities\Article';
    $article = new \Psc\Doctrine\TestEntities\Article('Lorem Ipsum:', 'Lorem Ipsum Dolor sit amet... <more>');
    $article->setId(7);
    
    $this->loadEntity($entityName);
    $object = $article->export();
    
    $this->assertInstanceOf('stdClass',$object);
    $this->assertAttributeEquals(7, 'id', $object);
    $this->assertAttributeEquals('Lorem Ipsum:', 'title', $object);
    $this->assertAttributeEquals('Lorem Ipsum Dolor sit amet... <more>', 'content', $object);
    $this->assertAttributeEquals(array(), 'tags', $object);
  }
  
  public function testGetTabsContentItem() {
    $tci = $this->defaultEntity->getTabsContentItem(TabsContentItem2::CONTEXT_DEFAULT);
    
    $this->assertInstanceOf('Psc\CMS\ContextTabsContentItem',$tci);
    $this->assertEquals($this->defaultEntity->getTabsLabel(TabsContentItem2::CONTEXT_DEFAULT), $tci->getTabsLabel());
    $this->assertNotEmpty($tci->getTabsURL()); // hier müsste dann irgendwie autoComplete sein oder sowas
  }

  public function testTabsLabel() {
    $this->assertNotEmpty($this->defaultEntity->getTabsLabel());
  }
  
  public function testTabsId() {
    $entity = $this->createInstance(3);
    $tabsId = $entity->getTabsId();
    
    $this->assertInternalType('array',$tabsId);
    $this->assertGreaterThanOrEqual(3,count($tabsId));
    
    $this->assertEquals(array('entities','mycmsentity', 3, 'form'), $tabsId);
  }
  
  public function testActionDefault() {
    $this->assertEquals('form',$this->defaultEntity->getTabsAction());
  }
  
  public function testGetSetAction() {
    $this->assertChainable($this->defaultEntity->setTabsAction('blubb'));
    $this->assertEquals('blubb',$this->defaultEntity->getTabsAction());
  }
  
  public function testDataDefault() {
    $this->assertEquals(array(),$this->defaultEntity->getTabsData());
  }
  
  public function testSetGetTabsData() {
    $entity = $this->createInstance(2);
    
    $this->assertChainable($entity->setTabsData(array('some'=>'stuff','in'=>'array')));
    $this->assertEquals(array('some'=>'stuff','in'=>'array'), $entity->getTabsData());
  }
  
  protected function createInstance($identifier = NULL) {
    return new MyCMSEntity($identifier);
  }
}

class MyCMSEntity extends AbstractEntity implements ComponentsCreaterListener {
  
  protected $identifier;
  
  protected $birthday;
  
  public function onBirthdayComponentCreated($component, $creater, $event) {
    
  }
  
  public function __construct($identifier) {
    $this->identifier = $identifier;
  }
  
  public function getIdentifier() {
    return $this->identifier;
  }
  
  public function getBirthday() {
    return $this->birthday;
  }
  
  public function setBirthday(DateTime $birthday) {
    $this->birthday = $birthday;
    return $this;
  }

  public function getTabsURL(Array $qvars = array()) {
    return NULL;
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'identifier' => new \Psc\Data\Type\IdType(),
      'birthday' => new \Psc\Data\Type\BirthdayType()
    ));
  }
  
  public function getEntityName() {
    return 'Psc\CMS\MyCMSEntity';
  }
}
?>