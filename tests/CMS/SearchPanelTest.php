<?php

namespace Psc\CMS;

class SearchPanelTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $searchPanel;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\SearchPanel';
    parent::setUp();
    
  }
  
  public function testAcceptance() {
    $searchPanel = new SearchPanel((object) array('label'=>'Sprecher',
                                                  'genitiv'=>'eines Sprechers',
                                                  'fields'=>'Namen oder Identifikationsnummer',
                                                  'url'=>'/autocomplete/go/search'
                                                  )
                                   );
    
    
    $this->html = $searchPanel->html();
    
    $this->test->css('fieldset.psc-cms-ui-group',$this->html)
      ->count(1)
      ->test('legend')
        ->count(1)
        ->hasText('Sprecher-Suche')
        ->end()
      ->test('div.content input.autocomplete[type="text"][name="identifier"]')
        ->count(1)
        ->end()
      ->test('small.hint')
        ->count(2)
    ;
  }
}
?>