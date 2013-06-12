<?php

namespace Psc\CMS\Controller;

use Psc\Entities\NavigationNode;
use Psc\Code\Code;

class NavigationControllerTest extends \Psc\Doctrine\NavigationNodesTestCase {

  public function setUp() {
    parent::setUp();

    $this->controller = new \Psc\Test\Controllers\NavigationNodeController($this->getDoctrinePackage(), $this->container = $this->createContainer());
  }

  public function testPre() {
    $this->assertInstanceOf('Psc\CMS\Controller\NavigationController', $this->controller);
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

  public function testGetMergedFlatForUI() {
    $this->assertInternalType('array', $this->controller->getMergedFlatForUI($this->defaultLanguage, $this->languages));

    $this->markTestIncomplete('TODO: should return all contexts merged. (aka main+footer+head for internal links e.g.');
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
  public function testPersistFooterFlatUIWithRemovedNode_throughSaveEntity($fixture, $context) {
    if ($context !== 'FoodCategories') return;

    $this->resetDatabaseOnNextTest();
    $this->controller->setContext($context);
    
    $flat = $this->getFlatForUI($this->defaultLanguage, $this->languages);
    
    // wir faken das was navigation.js machen würde, ansonsten ists schon bidirektional
    $flat = $this->fakeFrontendBehaviour($flat, function ($node) {
      return $node->slug->de !== 'oranges';
    });

    $this->controller->setContext('otherContext');

    $response = $this->controller->saveEntity($context, (object) $flat);
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

    $this->assertEquals($response['flat'], $this->getFlatForUI($this->defaultLanguage, $this->languages));
  }

  /**
   * @dataProvider getFixtures
   */
  public function testGetEntityChangesTheContextOfControllerANdRepository($fixture, $context) {
    $this->controller->getEntity($context, 'form');

    $this->assertEquals($context, $this->controller->getContext());
    $this->assertEquals($context, $this->controller->getRepository()->getContext());
  }

  /**
   * @dataProvider getFoodFixture
   */
  public function testPersistFlatUIWithRemovedNodeAndUpdateAndDelete($fixture, $context) {

    $this->resetDatabaseOnNextTest();
    $this->controller->setContext($context);
    
    $flat = $this->getFlatForUI($this->defaultLanguage, $this->languages);
    
    // frontend
    $flat[] = $this->createNewFlatNode(
      'Süßigkeiten',
      $this->hydrateNode('Food')
    );
  }


  public function testNewlyCreatedNodeHasAnInactivePageAttachedWithSlugLikeNavigationNode() {
    $this->resetDatabaseOnNextTest();
    
    $flatNode = $this->createNewFlatNode(
      'Süßigkeiten',
      $parent = $this->hydrateNode('Food')
    );

    $node = $this->controller->createNewNode($flatNode);
    $this->assertInstanceOf('Psc\Entities\NavigationNode', $node);
    $this->assertTrue($node->isNew(), 'node should be new');

    $this->assertInstanceOf('Psc\Entities\Page', $page = $node->getPage());
    $this->assertTrue($page->isNew(), 'page should be new');
    $this->assertFalse($page->isActive(), 'page should be inactive');

    $this->assertNotEmpty($node->getSlug($this->defaultLanguage));
    $this->assertEquals($node->getSlug($this->defaultLanguage), $page->getSlug($this->defaultLanguage));

    $this->assertTrue($this->em->getUnitOfWork()->isScheduledForInsert($page), 'page should be already persisted');

    $this->assertCount(count($this->languages), $page->getContentStreamsByLocale(), 'content streams should be filled');

    $this->em->clear();
  }

  /**
   * @dataProvider getFixtures
   */
  public function testGetFormularIsCalledThroughGetEntity($fixture, $context) {
    $this->html = $this->controller->getEntity($context, 'form', NULL);

    $this->test->css('form')->count(1)->attribute('action', $this->equalTo('/entities/navigation-node/'.$context))
       ->css('.psc-cms-ui-navigation')->count(1)->end()
       ->css('.psc-cms-ui-button-save')->count(1)->end()
       ->css('.psc-cms-ui-button-save')->count(1)->end()
    ;
  }

  public function testGetPagesMenu() {
    $this->assertInstanceOf('Psc\UI\PagesMenu', $pagesMenu = $this->controller->getPagesMenu());

    $pagesMenu->html();

    $this->test->joose($pagesMenu->getJooseSnippet())
      ->constructsJoose('Psc.UI.PagesMenu', array(
        'locale'=>$this->container->getLanguage()
      ));
  }

  protected function getFlatForUI($displayLocale, Array $languages) {
    $nodes = $this->repository->childrenQueryBuilder()->getQuery()->getResult();
    return $this->controller->getFlatForUI($nodes, $displayLocale, $languages);
  }

  protected function createContainer() {
    $fqns = array(
      'Page'=>'Psc\Entities\Page',
      'ContentStream'=>'Psc\Entities\ContentStream\ContentStream',
      'NavigationNode'=>'Psc\Entities\NavigationNode'
    );
    $container = $this->getMockForAbstractClass('Psc\CMS\Roles\Container');
    $container->expects($this->any())->method('getRoleFQN')->will($this->returnCallback(function ($name) use ($fqns) {
      return $fqns[$name];
    }));

    $container->expects($this->any())->method('getLanguage')->will($this->returnvalue($this->defaultLanguage));
    $container->expects($this->any())->method('getLanguages')->will($this->returnvalue($this->languages));

    $container->expects($this->any())->method('getTranslationContainer')->will($this->returnvalue($this->getTranslationContainer()));

    $controllers = array(
      'Page'=>new \Psc\Test\Controllers\PageController($this->dc, $container)
    );

    $container->expects($this->any())->method('getController')->will($this->returnCallback(function ($name) use ($controllers) {
      return $controllers[$name];
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
