<?php

namespace Psc\CMS\Controller;

require_once __DIR__.DIRECTORY_SEPARATOR.'AbstractEntityControllerBaseTest.php';

/**
 * @group class:Psc\CMS\Controller\AbstractEntityController
 */
class AbstractEntityControllerFormularTest extends AbstractEntityControllerBaseTest {

  public function testGetEntity_asFormular() {
    $this->requestFormPanel();
  }
  
  public function testOnComponentsCallbackGetsCalled() { // bababa: schön wäre ja auch ob die richtige component ankommt, gell
    $this->controller->expects($this->once())->method('onContentComponentCreated')->with($this->isInstanceOf('Psc\UI\Component\Base'));
    
    $this->requestFormPanel();
  }
  
  public function testEntityFormPanelGetsComponentHintFromPropertyHint() {
    $this->markTestIncomplete('TODO');
  }

  protected function requestFormPanel() {
    $this->expectRepositoryHydrates($this->article);
    $this->assertInstanceOf('Psc\CMS\EntityFormPanel', $formPanel = $this->controller->getEntity($this->article->getIdentifier(), 'form'));
    return $formPanel;
  }
}
?>