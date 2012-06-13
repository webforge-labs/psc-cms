<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\EntitySearchPanel
 */
class EntitySearchPanelTest extends \Psc\Code\Test\Base {
  
  protected $entitySearchPanel;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntitySearchPanel';
    parent::setUp();
    $this->meta = new \Psc\Doctrine\TestEntities\ArticleEntityMeta;
    $this->entitySearchPanel = new EntitySearchPanel($this->meta);
  }
  
  public function testAcceptance() {
    $this->html = $this->entitySearchPanel->html();
    
    $this->test->css('fieldset.psc-cms-ui-group',$this->html)->count(1);
    
    $this->assertNotNull($this->entitySearchPanel->getAutoCompleteUrl());
  }
}
?>