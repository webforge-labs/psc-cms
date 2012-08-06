<?php

namespace Psc\CMS\Navigation;

/**
 * @group class:Psc\CMS\Navigation\Repository
 */
class RepositoryTest extends \Psc\Doctrine\DatabaseTest {
  
  protected $repository;
  
  public function setUp() {
    $this->con = 'tests';
    $this->chainClass = 'Psc\CMS\Navigation\Repository';
    parent::setUp();
    
    $this->gedmo = \Psc\PSC::getProject()->getModule('Gedmo');
    $this->gedmo->initEventManager($this->em->getEventManager());
    
    $this->repository = $this->em->getRepository('Psc\CMS\Navigation\Node');
    
    $this->initData();
    
    $this->markTestSkipped('wir brauchen ein neues Konzept für die Tests, die ein Entity brauchen. Hier ist Navigation Node auch outdated. Siehe comun für tests');
  }
  
  protected function loadNaviFixture() {
    $this->truncateTable(array('navigation_nodes', 'navigation_node_translations'));
    $this->em->clear();
    
    $em = $this->em;
    $this->em->getConnection()->beginTransaction();
    $createNode = function ($title, $enTitle, $parent = NULL) use ($em){
      $node = new Node;
      $node->setTitle($title);
      if (isset($parent))
        $node->setParent($parent);
      
      $node->addTranslation(new NodeTranslation('en', 'title', $enTitle));
    
      $em->persist($node);
      return $node;
    };
  
    $cars = $createNode('Autos', 'Cars');
      $createNode('BMW', 'en BMW', $cars);
      $createNode('Mercedes Benz', 'en mercedes benz', $cars);
  
    $ls = $createNode('Lautsprecher','Speakers');
      $quadral = $createNode('Quadral', 'en quadral', $ls);
        $platinum = $createNode('Platinum', 'en platinum', $quadral);
        $aurum = $createNode('Aurum', 'en aurum', $quadral);
          $createNode('Vier', 'four', $aurum);
          $createNode('Titan MK VII', 'en Titan MK VII', $aurum);
      $canton = $createNode('Canton', 'en Canton', $ls);
  
    $this->em->flush();
    $this->em->getConnection()->commit();
  }
  
  public function testGetFlatForUI() {
    $this->loadNaviFixture();
    $flat = $this->repository->getFlatForUI();
    
    $this->assertEquals((object) array(
      'id'=>1,
      'title'=>array('de'=>'Autos','en'=>'Cars'),
      'slug'=>array('de'=>'autos'), // @Fixme geht noch nicht! ,'en'=>'cars'),
      'level'=>0,
      'locale'=>'de'
      ),
      $flat[0]
    );

    $this->assertEquals((object) array(
      'id'=>2,
      'title'=>array('de'=>'BMW','en'=>'en BMW'),
      'slug'=>array('de'=>'bmw'), // @FIXME ,'en'=>'en-bmw'),
      'level'=>1,
      'locale'=>'de'
      ),
      $flat[1]
    );

    $this->assertEquals((object) array(
      'id'=>8,
      'title'=>array('de'=>'Vier','en'=>'four'),
      'slug'=>array('de'=>'vier'),
      'level'=>3,
      'locale'=>'de'
      ),
      $flat[7]
    );
    
    //print \Psc\JS\Helper::reformatJSON(json_encode($flat));
  }
  
  
  /**
   * @group persist
   */
  public function testPersistFromUI_EmptyTable() {
    $this->truncateTable(array('navigation_nodes', 'navigation_node_translations'));
    $this->repository->persistFromUI(json_decode($this->uiJSON));
    
    $xml = $this->repository->childrenHierarchy(null, FALSE, array('decorate'=>true));
    $this->assertNotEmpty($xml, 'XML von childrenHiararchy() is not empty');
    $this->assertXmlStringEqualsXmlString($this->fixtureAsXML,$xml);
  }

  /**
   * @group persist
   */
  public function testPersistFromUI_FixtureTable() {
    $this->loadNaviFixture();
    $this->repository->persistFromUI(json_decode($this->uiJSON));
    
    $xml = $this->repository->childrenHierarchy(null, FALSE, array('decorate'=>true));
    $this->assertXmlStringEqualsXmlString($this->fixtureAsXML,$xml);
  }
  
  protected function initData() {
    $this->uiJSON = '[{"id":1,"title":{"de":"Autos","en":"Cars"},"parent":null,"level":0,"guid":"000001"},{"id":2,"title":{"de":"BMW","en":"en BMW"},"parent":{"id":1,"title":{"de":"Autos","en":"Cars"},"parent":null,"level":0,"guid":"000001"},"level":1,"guid":"000002"},{"id":3,"title":{"de":"Mercedes Benz","en":"en mercedes benz"},"parent":{"id":1,"title":{"de":"Autos","en":"Cars"},"parent":null,"level":0,"guid":"000001"},"level":1,"guid":"000003"},{"id":4,"title":{"de":"Lautsprecher","en":"Speakers"},"parent":null,"level":0,"guid":"000004"},{"id":5,"title":{"de":"Quadral","en":"en quadral"},"parent":{"id":4,"title":{"de":"Lautsprecher","en":"Speakers"},"parent":null,"level":0,"guid":"000004"},"level":1,"guid":"000005"},{"id":6,"title":{"de":"Platinum","en":"en platinum"},"parent":{"id":5,"title":{"de":"Quadral","en":"en quadral"},"parent":{"id":4,"title":{"de":"Lautsprecher","en":"Speakers"},"parent":null,"level":0,"guid":"000004"},"level":1,"guid":"000005"},"level":2,"guid":"000006"},{"id":7,"title":{"de":"Aurum","en":"en aurum"},"parent":{"id":5,"title":{"de":"Quadral","en":"en quadral"},"parent":{"id":4,"title":{"de":"Lautsprecher","en":"Speakers"},"parent":null,"level":0,"guid":"000004"},"level":1,"guid":"000005"},"level":2,"guid":"000007"},{"id":8,"title":{"de":"Vier","en":"four"},"parent":{"id":7,"title":{"de":"Aurum","en":"en aurum"},"parent":{"id":5,"title":{"de":"Quadral","en":"en quadral"},"parent":{"id":4,"title":{"de":"Lautsprecher","en":"Speakers"},"parent":null,"level":0,"guid":"000004"},"level":1,"guid":"000005"},"level":2,"guid":"000007"},"level":3,"guid":"000008"},{"id":9,"title":{"de":"Titan MK VII","en":"en Titan MK VII"},"parent":{"id":7,"title":{"de":"Aurum","en":"en aurum"},"parent":{"id":5,"title":{"de":"Quadral","en":"en quadral"},"parent":{"id":4,"title":{"de":"Lautsprecher","en":"Speakers"},"parent":null,"level":0,"guid":"000004"},"level":1,"guid":"000005"},"level":2,"guid":"000007"},"level":3,"guid":"000009"},{"id":10,"title":{"de":"Canton","en":"en Canton"},"parent":{"id":4,"title":{"de":"Lautsprecher","en":"Speakers"},"parent":null,"level":0,"guid":"000004"},"level":1,"guid":"000010"}]';
    
    $this->fixtureAsXML ='<ul><li>Autos<ul><li>BMW</li><li>Mercedes Benz</li></ul></li><li>Lautsprecher<ul><li>Quadral<ul><li>Platinum</li><li>Aurum<ul><li>Vier</li><li>Titan MK VII</li></ul></li></ul></li><li>Canton</li></ul></li></ul>';
  }
}
?>