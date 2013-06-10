<?php

namespace Psc\CMS;

use Psc\UI\PanelButtons;

/**
 * @group class:Psc\CMS\EntityViewPackage
 */
class EntityViewPackageTest extends \Psc\Code\Test\Base {
  
  protected $ev;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntityViewPackage';
    parent::setUp();
    $this->ev = new EntityViewPackage($this->getTranslationContainer());
    
    $this->entityForm = $this->getMockBuilder('Psc\CMS\EntityForm')->disableOriginalConstructor()->getMock();
  }
  
  public function testDPIGeneration() {
    $this->assertInstanceOf('Psc\CMS\ComponentMapper',$this->ev->getComponentMapper());
    $this->assertInstanceOf('Psc\CMS\Labeler',$this->ev->getLabeler());
  }
  
  public function testEntityFormPanelIsInjectedWithPanelButtons() {
    $this->ev->setPanelButtons(
      $buttons = new PanelButtons(array('preview', 'save', 'reload'), $this->getTranslationContainer())
    );
    
    $panel = $this->ev->createFormPanel('some label', $this->entityForm);
    
    $this->assertSame($buttons, $panel->getPanelButtons(), 'panel buttons were not injected to formPanel');
  }
}
