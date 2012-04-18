<?php

namespace Psc\UI;

use Psc\Doctrine\TestEntities\Tag;
use Psc\Doctrine\TestEntities\Article;
use Psc\Doctrine\TestEntities\TagEntityMeta;
use Psc\Doctrine\TestEntities\ArticleEntityMeta;
use Psc\CMS\AjaxMeta;

class ComboDropBox2Test extends \Psc\Code\Test\HTMLTestCase {
  
  protected $comboDropBox;
  
  public function setUp() {
    //$this->chainClass = 'Psc\UI\ComboDropBox2';
    parent::setUp();
    
    $classMetadata = $this->getMock('Doctrine\ORM\Mapping\ClassMetadata', array(), array('Psc\Doctrine\TestEntitites\Article'));
    $this->entityMeta = new TagEntityMeta($classMetadata);
    $this->avaibleItems = new AjaxMeta(AjaxMeta::GET, '/entities/tags/');
    
    $this->marktestIncomplete('not yet ready');
    
    $this->comboBox = new ComboBox2($this->entityMeta, $this->avaibleItems);
    $this->dropBox = new ComboBox2($this->entityMeta);
    $this->comboDropBox = new ComboDropBox2($this->comboBox, $this->dropBox, 'Tags Pflegen', 'tags');
  }
  
  
  public function testAcceptance() {
    extract($this->loadTestEntities('articles'));
    $tags = $this->loadTestEntities('tags');

    $this->html = $this->comboDropBox->html();
    
    // fieldset mit richtigem laben aussen rum
    $this->test->css('fieldset.psc-cms-ui-group', $this->html)
      ->count(1)
      ->test('legend')->count(1)->hasText('Tags Pflegen')-end()
      ->test('div.content')
        ->count(1)
        // combo box
        ->test('input.psc-cms-ui-combo-box')
          ->count(1)
          ->hasAttribute('type','text')
          ->end()
        
        // button neben der combo-box zum öffnen (kommt aus js)
        //->test('button.psc-cms-ui-button')
        //  ->count(1)
        //  ->test('span.ui-icon-triangle-1-s')->count(1)->end()
        //  ->end()
       
        // dropbox
        ->test('div.psc-cms-ui-drop-box')
          ->count(1)
    ;
  }
  
  public function testAssignedItemsShowAsButtonsInDropBox() {
  }
}
?>