<?php

namespace Psc\UI\Component;

use Psc\CMS\AjaxMeta;
use Psc\Code\Code;

class ComboDropBoxBuilderTest extends \Psc\Code\Test\Base {
  
  protected $comboDropBoxBuilder;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\Component\ComboDropBoxBuilder';
    parent::setUp();
    
  }
  
  public function testAcceptance() {
    extract($this->loadTestEntities('articles'));
    $tags = $this->loadTestEntities('tags');
    $article = $a1;
    
    $article->addTag($tags['t1']);
    $article->addTag($tags['t3']);
    
    $this->builder = new ComboDropBoxBuilder();
    
    // eine combo drop box "pflegt" eine Collection eines Entities
    
    // die Einträge der ComboBox können direkt angegeben werden (entitiesCollection)
    $this->builder->setFromEntity($article,'tags',$tags);
    
    //  oder mit Ajax geladen werden
    //$this->builder->setFromEntity($article,'tags',AjaxMeta $ajaxMeta);
    $this->builder->setMultiple(FALSE);

    $component = $this->builder->buildComponent();
    
    $this->assertEquals('tags', $component->getFormName());
    $this->assertEntityCollectionEquals($article->getTags(), $component->getAssignedItems());
    $this->assertEntityCollectionEquals(Code::castCollection($tags), Code::castCollection($component->getAvaibleItems()));
    $this->assertFalse($component->getMultiple());
    $this->assertEquals('zugeordnete Tags',$component->getFormLabel());
    
    $component2 = $this->builder->buildComponent();
    $this->assertNotSame($component, $component2);

    $this->builder->setFromEntity($article,'tags',new AjaxMeta('GET','/entities/tags'));
    $component = $this->builder->buildComponent();
    
    // nicht schön, aber hey, hier muss eh alles neu
    $this->assertEquals('/entities/tags',$component->getWidget()->getComboBox()->getWidgetOptions()->ajaxUrl);
    $this->assertEquals('GET',$component->getWidget()->getComboBox()->getWidgetOptions()->ajaxMethod);
  }
}
?>