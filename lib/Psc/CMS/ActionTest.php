<?php

namespace Psc\CMS;

use Psc\Doctrine\TestEntities\Tag;

class ActionTest extends \Webforge\Code\Test\Base {
  
  protected $entity, $entityMeta;
  protected $specificAction, $generalAction;
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Action';
    parent::setUp();
    $this->dc = $this->doublesManager->createDoctrinePackageMock();
    
    $this->entity = new Tag('nice-label');
    $this->entityMeta = $this->getMockBuilder('Psc\CMS\EntityMeta')->disableOriginalConstructor()->getMock();
    
    $this->specificAction = new Action($this->entity, ActionMeta::POST, 'relevance');
    $this->generalAction = new Action($this->entityMeta, ActionMeta::GET, 'infos');
  }
    
  public function testActionConstructedWithEntityIsSpecificType() {
    $this->assertEquals(Action::SPECIFIC, $this->specificAction->getType());
  }
  
  public function testActionConstructedWithEntityMetaIsGeneralType() {
    $this->assertEquals(Action::GENERAL, $this->generalAction->getType());
  }
  
  protected function expectDCWillReturnEntityMeta() {
    $this->dc->expects($this->once())->method('getEntityMeta')
             ->with($this->equalTo($this->entity->getEntityName()))
             ->will($this->returnValue($this->entityMeta));
  }
  
  public function testGetEntityMetaReturnsEntityMetaForSpecificTypeWhenDCIsGiven() {
    $this->expectDCWillReturnEntityMeta();
    $this->assertInstanceOf('Psc\CMS\EntityMeta', $this->specificAction->getEntityMeta($this->dc));
  }
  
  public function testGetEntityMetaReturnsEntityMetaForGeneralTypeWhenDCIsGiven() {
    $this->assertInstanceOf('Psc\CMS\EntityMeta', $this->generalAction->getEntityMeta($this->dc));
  }

  public function testGetEntityMetaReturnsEntityMetaForGeneralType_WhenDCIsNotGiven() {
    $this->assertInstanceOf('Psc\CMS\EntityMeta', $this->generalAction->getEntityMeta());
  }  

  public function testGetEntityMetaThrowsExceptionWhenEntityMetaForSpecificIsCalledWithoutDC() {
    $this->setExpectedException('LogicException');
    $this->assertInstanceOf('Psc\CMS\EntityMeta', $this->specificAction->getEntityMeta());
  }
}
?>