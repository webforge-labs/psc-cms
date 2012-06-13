<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\EntityGridPanel
 */
class EntityGridPanelTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $entityGridPanel;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntityGridPanel';
    parent::setUp();
    
    $this->entities = $this->loadTestEntities('users');
    //$this->classMetadata = $this->getMock('Doctrine\ORM\Mapping\ClassMetadata', array('getIdentifierFieldNames'), array('Psc\Doctrine\TestEntitites\User'));
    //$this->classMetadata->expects($this->any())->method('getIdentifierFieldNames')->will($this->returnValue(array('email')));
    $this->classMetadata = \Psc\PSC::getProject()->getModule('Doctrine')->getEntityManager()->getClassMetadata('Entities\User');
    $this->entityMeta = new \Psc\CMS\UserEntityMeta('Entities\User', $this->classMetadata);
    $this->entityGridPanel = new EntityGridPanel($this->entityMeta, 'User Verwaltung'); 
  }
  
  public function testAcceptance() {
    $this->entityGridPanel->addControlColumn();
    $this->entityGridPanel->addProperty('email', EntityGridPanel::TCI_BUTTON);
    $this->entityGridPanel->addEntities($this->entities);
    
    $this->html = $this->entityGridPanel->html();
    $this->test->css('fieldset.psc-cms-ui-entity-grid-panel-container')->count(1);
    
    $this->test->css('table.psc-cms-ui-entity-grid-panel tr th.email')
      ->count(1)
      ->hasText('E-Mail') // magic :>
    ;

    // eine th.ctrl im header
    $this->test->css('table.psc-cms-ui-entity-grid-panel tr th.ctrl')
      ->count(1)
    ;
    
    // erste zeile muss .first sein
    $this->test->css('table.psc-cms-ui-entity-grid-panel tr:nth-of-type(1)')
      ->count(1)
      ->hasClass('first')
    ;
    
    $this->test->css('table tr')
      ->count(1+count($this->entities)); // 1 head + x data

    // jede zeile muss td.ctrl haben
    $this->test->css('table.psc-cms-ui-entity-grid-panel tr td.ctrl')
      ->count(count($this->entities)) // nicht +1 weil in head eine th ist
    ;

    // jede zeile muss td.tci haben, darin ist ein button
    $this->test->css('table.psc-cms-ui-entity-grid-panel tr td.tci')
      ->count(count($this->entities)) // nicht +1 weil in head eine th ist
    ;
    
    $this->test->css('table.psc-cms-ui-entity-grid-panel tr td.tci button.psc-cms-ui-button')
      ->count(count($this->entities))
    ;
    
    /* new button muss da sein */
    $this->test->css('button.psc-cms-ui-button-new')
      ->count(1);
  }
}
?>