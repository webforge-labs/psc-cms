<?php

namespace Psc\UI;

use Psc\JS\jQuery;

/**
 * @group class:Psc\UI\PanelButtons
 */
class PanelButtonsTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $panelButtons;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\PanelButtons';
    parent::setUp();

    $this->translationContainer = $this->createTranslationContainer(
      $this->buildTranslations('cms')
        ->locale('de')
          ->trans('panel.buttons.save', 'speichern')
          ->trans('panel.buttons.reload', 'neu laden')
          ->trans('panel.buttons.close', 'schließen')
          ->trans('panel.buttons.save-close', 'speichern und schließen')
          ->trans('panel.buttons.insert', 'neu erstellen')
          ->trans('panel.buttons.insert-open', 'neu erstellen und geöffnet lassen')
          ->trans('panel.buttons.reset', 'zurücksetzen')
          ->trans('panel.buttons.preview', 'vorschau')

         ->locale('en')
          ->trans('panel.buttons.save', 'save')
          ->trans('panel.buttons.reload', 'reload')
          ->trans('panel.buttons.close', 'close')
          ->trans('panel.buttons.save-close', 'save and close')
          ->trans('panel.buttons.insert', 'insert')
          ->trans('panel.buttons.insert-open', 'save and open in new tab')
          ->trans('panel.buttons.reset', 'reset')
          ->trans('panel.buttons.preview', 'preview')
    );
  }
  
  public function testAcceptance() {
    $this->panelButtons = new PanelButtons(array('save','reload','save-close'), $this->translationContainer);
    
    $this->html = $this->panelButtons->html();
    
    $this->test->css('div.psc-cms-ui-buttonset.psc-cms-ui-buttonset-right')
      ->count(1)
      ->css('button.psc-cms-ui-button-left.psc-cms-ui-button-save')->count(1)->end()
      ->css('button.psc-cms-ui-button-left.psc-cms-ui-button-reload')->count(1)->end()
      ->css('button.psc-cms-ui-button-left.psc-cms-ui-button-save-close')->count(1)->end()
    ;

    $this->test->css('div.psc-cms-ui-buttonset.psc-cms-ui-buttonset-right + div.clear')
      ->count(1);
  }
  
  public function testAddNewButtonWithOwnButtonInstance() {
    $this->panelButtons = new PanelButtons(array('reload'), $this->translationContainer);
    $newButton = $this->panelButtons->addNewButton(new Button('My Nice New Button'));
    $this->assertInstanceof('Psc\UI\ButtonInterface', $newButton);
    
    $this->assertNotEmpty($newButton->getLeftIcon());
  }
  
  public function testPreviewButton() {
    $this->panelButtons = new PanelButtons(array('preview','save','reload','save-close'), $this->translationContainer);
    
    $this->html = $this->panelButtons->html();
    
    $this->test->css('div.psc-cms-ui-buttonset')
      ->count(1)
      ->css('button.psc-cms-ui-button-preview')->count(1)->end()
    ;
  }

  public function testViewButton() {
    $this->panelButtons = new PanelButtons(array('view','reload'), $this->translationContainer);
    
    $this->html = $this->panelButtons->html();
    
    $this->test->css('div.psc-cms-ui-buttonset')
      ->count(1)
      ->css('button.psc-cms-ui-button-view')->count(1)->end()
    ;
  }

  protected function setupAllButtons() {
    $buttons = array(
      'save',
      'reload',
      'save-close',
      'insert',
      'insert-open',
      'reset',
      'preview'
    );

    $this->panelButtons = new PanelButtons($buttons, $this->translationContainer);

    return count($buttons);
  }

  public function testInternationalisationOfPanelButtonsDE() {
    $this->translationContainer->setLocale('de');
    $count = $this->setupAllButtons();

    $this->assertEquals(
      array(
        'speichern',
        'neu laden',
        'speichern und schließen',
        'neu erstellen',
        'neu erstellen und geöffnet lassen',
        'zurücksetzen',
        'vorschau',
      ),
      $this->getPanelButtonsLabels($count)
    );
  }

  public function testInternationalisationOfPanelButtonsEN() {
    $this->translationContainer->setLocale('en');
    $count = $this->setupAllButtons();

    $this->assertEquals(
      array(
        'save',
        'reload',
        'save and close',
        'insert',
        'save and open in new tab',
        'reset',
        'preview',
      ),
      $this->getPanelButtonsLabels($count)
    );
  }

  protected function getPanelButtonsLabels($expectedNum) {
    $buttons = $this->test->css('div.psc-cms-ui-buttonset', $this->html = $this->panelButtons->html())
      ->count(1)
        ->css('button')->count($expectedNum)->getjQuery()
    ;

    $labels = array();
    foreach ($buttons as $button) {
      $button = new jQuery($button);

      $labels[] = $button->text();
    }

    return $labels;
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testInvalidButtonsParam() {
    new PanelButtons(array('save','save-close','relod'), $this->translationContainer); // look closely
  }
}
