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
  
  public function testCreateActionGenericGetAction() {
    $this->assertInstanceOf('Psc\CMS\Action', $action = $this->dp->createAction('person', 'GET', 'episodes'));
    $this->assertInstanceOf('Psc\CMS\EntityMeta', $action->getEntityMeta());
  }

  public function testCreateActionPersonSpecificEditAction() {
    $person = new Person('its me!');
    $this->assertInstanceOf('Psc\CMS\Action', $action = $this->dp->createAction($person, 'GET', 'form'));
    $this->assertSame($person, $action->getEntity());
  }
  
  public function testCreateButton() {
    return;
    $button = $this->dp->tabButton('person', 'GET', 'related')
      ->setLabel('verknüpfte Personen anzeigen');

    $this->assertInstanceOf('Psc\UI\Button', $button);
    $this->assertEquals('verknüpfte Personen anzeigen', $button->getLabel());
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