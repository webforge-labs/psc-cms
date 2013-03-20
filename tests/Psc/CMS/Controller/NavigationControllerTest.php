<?php

namespace Psc\CMS\Controller;

/**
 * @group class:Psc\CMS\Controller\NavigationController
 */
class NavigationControllerTest extends \Psc\Doctrine\DatabaseTestCase {
  
  protected $navigationController;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\NavigationController';
    parent::setUp();
    $this->navigationController = new NavigationController('default', $this->getDoctrinePackage());
  }

  public function testNavigationControllerReturnsTheFixtureAsText() {
    
  }
  
  public function testSaveAcceptance() {
    $this->navigationController->saveFormular(
      (object) array(
        'bodyAsJSON'=>'[{"id":1,"title":{"de":"CoMun"},"parent":null,"level":0,"guid":"000001"},{"id":2,"title":{"de":"Kooperation"},"parent":null,"level":0,"guid":"000002"},{"id":3,"title":{"de":"Städte und städtische Akteure"},"parent":{"id":2,"title":{"de":"Kooperation"},"parent":null,"level":0,"guid":"000002"},"level":1,"guid":"000003"},{"id":4,"title":{"de":"Deutsche Kooperationen"},"parent":{"id":2,"title":{"de":"Kooperation"},"parent":null,"level":0,"guid":"000002"},"level":1,"guid":"000004"},{"id":5,"title":{"de":"BMZ"},"parent":{"id":4,"title":{"de":"Deutsche Kooperationen"},"parent":{"id":2,"title":{"de":"Kooperation"},"parent":null,"level":0,"guid":"000002"},"level":1,"guid":"000004"},"level":2,"guid":"000005"},{"id":6,"title":{"de":"GIZ"},"parent":{"id":4,"title":{"de":"Deutsche Kooperationen"},"parent":{"id":2,"title":{"de":"Kooperation"},"parent":null,"level":0,"guid":"000002"},"level":1,"guid":"000004"},"level":2,"guid":"000006"},{"id":7,"title":{"de":"Strategische Partner"},"parent":{"id":2,"title":{"de":"Kooperation"},"parent":null,"level":0,"guid":"000002"},"level":1,"guid":"000007"},{"id":8,"title":{"de":"Projekte der Komunen"},"parent":null,"level":0,"guid":"000008"},{"id":9,"title":{"de":"Dialog"},"parent":null,"level":0,"guid":"000009"},{"id":10,"title":{"de":"Call for Projects"},"parent":null,"level":0,"guid":"000010"}]',
      )
    );
  }
}
?>