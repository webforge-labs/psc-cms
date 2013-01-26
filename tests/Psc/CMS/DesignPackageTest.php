<?php

namespace Psc\CMS;

use Psc\Doctrine\TestEntities\Person;

/**
 * @group class:Psc\CMS\DesignPackageTest
 *
 * this is experimental!
 */
class DesignPackageTest extends \Psc\Doctrine\DatabaseTestCase {
  
  protected $designPackageTest;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\DesignPackageTest';
    parent::setUp();
    $this->dp = new DesignPackage(
      $this->dc
    );
    
    /* actionMapper
     * name => action
     *
     * Router
     * action => requestmeta aka REST
     * REST => action
     *
     * Service
     * action => controller-method
     * 
     */
  }
  
  public function testActionCreatesGenericGetAction() {
    $this->assertInstanceOf('Psc\CMS\Action', $action = $this->dp->action('person', 'GET', 'episodes'));
    $this->assertInstanceOf('Psc\CMS\EntityMeta', $action->getEntityMeta());
    $this->assertEquals('person', $action->getEntityMeta()->getEntityName());
  }

  public function testActionCreatesPersonSpecificEditAction() {
    $person = new Person('its me!');
    $this->assertInstanceOf('Psc\CMS\Action', $action = $this->dp->action($person, 'GET', 'form'));
    $this->assertSame($person, $action->getEntity());
  }
  
  public function testTabButtonCreatesATabButtonInterfaceButton() {
    $button = $this->dp->tabButton(
      'verknüpfte Personen anzeigen',
      $this->dp->action('person', 'GET', 'related')
    );

    $this->assertInstanceOf('Psc\UI\ButtonInterface', $button);
    $this->assertInstanceOf('Psc\UI\TabButtonInterface', $button);
    $this->assertEquals('verknüpfte Personen anzeigen', $button->getLabel());
    
    $this->markTestIncomplete('How to test that action is converted to the correct requestmeta?');
  }
  
  public function testFullOIDSiteSyntax() {
    return;
    
    $designer = $this->dp;
    $components = $this->dp->getComponentsDesigner();
    
    $designer
      ->formPanel()
        ->action($oid, 'PUT')
        ->buttons(array('save','reload','save-close'))
        ->right()
          ->accordion
            ->section('Sounds & Texte', array(
              $designer->tabButton($soundIs),
              $designer->tabButton($soundIst)
            ))
            ->section('Wörterbuch', function () {
              $table = new \Psc\UI\Table();
              
              $table->tr();
              $table->td();
              // usw
              
              
              return array($table);
            })
            ->section('Meta', array(
              $componentDesigner->get($oid, 'label'),
              $componentDesigner->componentFor($oid, 'sounds'),
              $componentDesigner->componentFor($oid, 'pages')
            ))
            ->section('Optionen', array(
              $designer->button('OID löschen')
                ->action($oid, 'DELETE')
            ))
//          ->end()
//        ->end()
        ->left()
          ->group('Tippreihenfolge für Entdecken')
            ->group('1. Tippen')
              ->group('Act0')->end()
              ->checkbox('Random?', 'random')
              ->appendButton('weitere Aktion')
                ->action($product, 'GET', array('matrix-manager','create-action'))
              ->end()
          ->appendButton('weiterer Tipp')
            ->action($product, 'GET', array('matrix-manager','create-action'))
            
    
    ;
  }
}
?>