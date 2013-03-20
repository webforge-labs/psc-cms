<?php

namespace Psc\CMS\Controller;

use Psc\Entities\NavigationNode;
use Psc\Code\Code;

class NavigationControllerTest extends \Psc\Doctrine\NavigationNodesTestCase {

  public function setUp() {
    parent::setUp();

    $this->controller = new NavigationController('default', $this->getDoctrinePackage());
    $this->controller->setContainer($this->createContainer());
  }
  
  /**
   * @dataProvider getFixtures
   */
  public function testGetFlatForUI($fixture, $context) {
    $this->controller->setContext($context);

    $flat = $this->getFlatForUI($this->defaultLanguage, $this->languages);

    foreach ($flat as $node) {
      $this->assertArrayHasKey('de', (array) $node->title, Code::varInfo($node->title));
      //$this->assertArrayHasKey('fr', (array) $node->title, Code::varInfo($node->title));
      
      $this->assertArrayHasKey('de', (array) $node->slug, 'slug'.Code::varInfo($node->slug));
      $this->assertNotEmpty($node->slug->de);
      //$this->assertObjectHasAttribute('fr', $node->slug, 'slug'.Code::varInfo($node->slug));
      //$this->assertNotEmpty($node->slug->fr,'slug für fr ist nicht gesetzt'.Code::varInfo($node->slug));
      $this->assertEquals('de', $node->locale);
      
      $this->assertObjectHasAttribute('depth', $node, print_r(array_keys((array) $node), true));
    }
  }

  /**
   * @dataProvider getFixtures
   */
  public function testPersistFooterFlatUIWithNoChanges($fixture, $context) {
    $this->resetDatabaseOnNextTest();
    $this->controller->setContext($context);
    
    $flat = $this->getFlatForUI($this->defaultLanguage, $this->languages);
    
    // wir faken das was navigation.js machen würde, ansonsten ists schon bidirektional
    $flat = $this->fakeFrontendBehaviour($flat);

    $this->controller->saveFormular($flat);
    $this->em->clear();
    
    $this->assertEquals(
      $fixture->toString(), 
      $this->repository->getText($this->defaultLanguage),
      'saving without any changes'
    );
  }

  /**
   * @dataProvider getFixtures
   */
  public function testPersistFooterFlatUIWithRemovedNode($fixture, $context) {
    if ($context !== 'FoodCategories') return;

    $this->resetDatabaseOnNextTest();
    $this->controller->setContext($context);
    
    $flat = $this->getFlatForUI($this->defaultLanguage, $this->languages);
    
    // wir faken das was navigation.js machen würde, ansonsten ists schon bidirektional
    $flat = $this->fakeFrontendBehaviour($flat, function ($node) {
      return $node->slug->de !== 'oranges';
    });

    $response = $this->controller->saveFormular($flat);
    $this->em->clear();
    
    $this->assertEquals(
"Food
  Vegetables
  Fruits
    Citrons
  Milk
  Meat
    Beef
    Ham
",

      $this->repository->getText($this->defaultLanguage),
      'saving with removing oranges: '.
      "\n".$response['log']
    );
  }

  /**
   * @dataProvider getFixtures
   */
  public function testPersistFooterFlatUIWithRemovedNodeAndUpdateAndDelete($fixture, $context) {
    if ($context !== 'FoodCategories') return;

    $this->resetDatabaseOnNextTest();
    $this->controller->setContext($context);
    
    $flat = $this->getFlatForUI($this->defaultLanguage, $this->languages);
    
    // frontend
    $flat[] = $this->createNewFlatNode(
      'Süßigkeiten',
      $this->hydrateNode('Food')
    );

    $flat = $this->fakeFrontendBehaviour($flat, function ($node) {
      if ($node->slug->de === 'Food') {
        $node->slug->de = 'Futter';
      }

      return $node->slug->de !== 'oranges';
    });
    // end frontend


    $response = $this->controller->saveFormular($flat);
    $this->em->clear();
    
    $this->assertEquals(
"Food
  Vegetables
  Fruits
    Citrons
  Milk
  Meat
    Beef
    Ham
",

      $this->repository->getText($this->defaultLanguage),
      'saving with removing oranges: '.
      "\n".$response['log']
    );
  }

  protected function getFlatForUI($displayLocale, Array $languages) {
    $nodes = $this->repository->childrenQueryBuilder()->getQuery()->getResult();
    return $this->controller->getFlatForUI($nodes, $displayLocale, $languages);
  }

  protected function createContainer() {
    $fqns = array(
      'Page'=>'Psc\Entities\Page',
      'NavigationNode'=>'Psc\Entities\NavigationNode'
    );
    $container = $this->getMockForAbstractClass('Psc\CMS\Roles\SimpleContainer');
    $container->expects($this->any())->method('getRoleFQN')->will($this->returnCallback(function ($name) use ($fqns) {
      return $fqns[$name];
    }));

    return $container;
  }

  protected function fakeFrontendBehaviour(Array $flat, \Closure $filter = NULL) {
    $guid = 0;
    $byId = array();
    foreach ($flat as $node) {
      $node->guid = ++$guid;
      $byId[$node->id] = $node;
    }

    if ($filter) {
      $flat = array_filter($flat, $filter);
    }

    foreach ($flat as $node) {
      if ($node->parentId > 0) {
        $node->parent = $byId[$node->parentId];
      }
    }

    return $flat;
  }

  protected function createNewFlatNode($title, $parent) {
    $titles = array($this->defaultLanguage=>$title);

    $node = new NavigationNode($titles);
    $node->setParent($parent);

    $flatted = $this->controller->getFlatForUI(array($node), $this->defaultLanguage, $this->languages);
    return current($flatted);
  }

  protected function debugFlat($nodes) {
    return "\n".\Psc\A::implode($nodes, "\n", function ($node) { return $node->title->de; });
  }
}
