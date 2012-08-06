<?php

namespace Psc\UI;

use \Psc\UI\FormPanel;

/**
 * @group class:Psc\UI\FormPanel
 */
class FormPanelTest extends \Psc\Code\Test\HTMLTestCase {
  
  public function testAcceptance() {
    $this->html = $formPanel = new FormPanel('Episoden verwalten', $form = new \Psc\CMS\Form(NULL, '/episodes/manager'));

    $this->test->css('form.psc-cms-ui-form')->count(1)->hasAttribute('action','/episodes/manager')
      ->test('div.psc-cms-ui-form-panel')->count(1)
      ->test('.psc-cms-ui-buttonset')->count(1)
        ->test('button.psc-cms-ui-button-reload')->count(1)->end()
        ->test('button.psc-cms-ui-button-save')->count(1)->end()
        ->test('button.psc-cms-ui-button-save-close')->count(1)->end()
        ->end()
      ->test('fieldset.psc-cms-ui-group')->count(1)
        ->test('legend')->count(1)->hasText('Episoden verwalten')->end()
        
      ->end()
    ;
  }
  
  public function testWithAccordion() {
    $this->html = $formPanel = new FormPanel('Episoden verwalten', $form = new \Psc\CMS\Form(NULL, '/episodes/manager'));
    
    $accordion = new \Psc\UI\Accordion(array('autoHeight'=>true, 'active'=>0));
    $accordion->addSection('Optionen', array());
    $accordion->addSection('Meta', array());
    
    $formPanel->addAccordion($accordion);
    
    $this->test->css('form.psc-cms-ui-form')->count(1)->hasAttribute('action','/episodes/manager')
      ->test('div.psc-cms-ui-form-panel')->count(1)
        ->test('.psc-cms-ui-buttonset')->count(1)->end()
        ->test('fieldset.psc-cms-ui-group')->count(1)
          ->test('legend')->count(1)->hasText('Episoden verwalten')->end()
        ->end()
        ->test('.psc-cms-ui-accordion')->count(1)->end()
    ;
  }
  
  public function testWithoutLabelRemovesFieldSet() {
    $this->html = $formPanel = new FormPanel(NULL, $form = new \Psc\CMS\Form(NULL, '/episodes/manager'));
    
    $this->test->css('div.psc-cms-ui-form-panel')->count(1)
      ->test('.psc-cms-ui-buttonset')->count(1)->end()
      ->test('fieldset')->count(0)->end();
  }
}

?>