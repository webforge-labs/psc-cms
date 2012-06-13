<?php

namespace Psc\UI;

use Psc\Doctrine\TestEntities\Tag;
use Psc\Doctrine\TestEntities\Article;

/**
 * @group class:Psc\UI\ComboDropBox2
 *
 * http://wiki.ps-webforge.com/psc-cms:dokumentation:ui:combodropbox
 */
class ComboDropBox2Test extends \Psc\Code\Test\HTMLTestCase {
  
  protected $comboDropBox;
  
  public function setUp() {
    //$this->chainClass = 'Psc\UI\ComboDropBox2';
    parent::setUp();
    
    $this->relationEntityMeta = $this->getEntityMeta('Psc\Doctrine\TestEntities\Tag');
    $this->avaibleItems = $this->relationEntityMeta->getAutoCompleteRequestMeta();
    
    //$this->entityMeta = $this->getEntityMeta('Psc\Doctrine\TestEntities\Article');
    
    $this->comboBox = new ComboBox2('tags', $this->avaibleItems, $this->relationEntityMeta);
    $this->dropBox = new DropBox2('tags', $this->relationEntityMeta, array());
    
    $this->comboDropBox = new ComboDropBox2($this->comboBox, $this->dropBox, 'Tags Pflegen');
  }
  
  
  /**
   * Wir müssen hier eigentlich nur die Verbindungen der beiden Testen
   *
   * ob dann die Items korrekt gesetzt werden usw wird durch andere Tests geprüft
   */
  public function testAcceptance() {
    $article = current($this->loadTestEntities('articles'));
    $tags = $this->loadTestEntities('tags');

    $this->html = $this->comboDropBox->html();
    
    // fieldset mit richtigem laben aussen rum
    $this->test->css('fieldset.psc-cms-ui-group', $this->html)
      ->count(1)
      ->hasClass('psc-cms-ui-combo-drop-box-wrapper') // macht abstand zwischen combo + dropbox
      ->test('legend')->count(1)->hasText('Tags Pflegen')->end()
      ->test('div.content')
        ->count(1)
        // combo box
        ->test('input.psc-cms-ui-combo-box')
          ->count(1)
          ->hasAttribute('type','text')
          ->attribute('name',$this->logicalNot($this->equalTo('tags')))
          ->end()
        
        // button neben der combo-box zum öffnen (kommt aus js)

        // dropbox
        ->test('div.psc-cms-ui-drop-box')
          ->count(1)
          ->end()
    ;
    
    $this->test->js($this->comboDropBox)
      ->constructsJoose('Psc.UI.ComboDropBox')
        ->hasParam('dropBoxWidget')
        ->hasParam('comboBoxWidget')
      ;
      
    $this->test->js($this->comboDropBox->getComboBox())
      ->constructsJoose('Psc.UI.ComboBox')
        ->hasParam('selectMode',$this->equalTo(false));
  }
}
?>