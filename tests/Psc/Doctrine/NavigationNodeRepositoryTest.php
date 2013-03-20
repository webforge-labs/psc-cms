<?php

namespace Psc\Doctrine;

use Psc\Code\Code;

class NavigationNodeRepositoryTest extends \Psc\Doctrine\NavigationNodesTestCase {
  
  /**
   * @dataProvider getFixtures
   */
  public function testInsertedFixtureEqualsOurFixture($fixture, $context) {
    $this->repository->setContext($context);

    $this->assertEquals(
      $fixture->toString(),
      $this->repository->getText('de')
    );
  }

  public function testGetHTMLCallsTheNestedSetConverterWithAllNodesFromContext_defaultIsEmpty() {
    $converter = $this->getMock('Webforge\CMS\Navigation\NestedSetConverter', array());

    $converter->expects($this->once())->method('toHTMLList')
      ->with(new \PHPUnit_Framework_Constraint_Count(0));

    $this->repository->getHTML('de', $snippets = array(), $converter);
  }

  /**
   * @dataProvider getFixtures
   */
  public function testGetHTMLCallsTheNestedSetConverterWithAllNodesFromContext($fixture, $context) {
    $this->repository->setContext($context);

    $converter = $this->getMock('Webforge\CMS\Navigation\NestedSetConverter', array());

    $converter->expects($this->once())->method('toHTMLList')
      ->with(new \PHPUnit_Framework_Constraint_Count($this->countFixture($fixture)));

    $this->repository->getHTML('de', $snippets = array(), $converter);
  }

  /**
   * @dataProvider getFixtures
   */
  public function testFindByContextUsesSetContextByDefault($fixture, $context) {
    $this->repository->setContext($context);

    $this->assertCount(
      $this->countFixture($fixture),
      $this->repository->findAllNodes()
    );
  }

  /**
   * @dataProvider getFixtures
   */
  public function testFindByContextUsesGivenContextPerArg($fixture, $context) {
    $this->repository->setContext('nonsense');

    $this->assertCount(
      count($fixture->toArray()),
      $this->repository->findAllNodes($context)
    );
  }

  public function testGetPathForOneNodeContainsAllNodesOnPathWithSelf() {
    $this->repository->setContext('FoodCategories');

    $food = $this->hydrateNode('food');
    $fruits = $this->hydrateNode('fruits');
    $citrons = $this->hydrateNode('citrons');
    
    $this->assertCollection(
      array($food, $fruits, $citrons),
      $this->repository->getPath($citrons)
    );

    $this->assertCollection(
      array($food),
      $this->repository->getPath($food)
    );
  }

  public function testGetURLConstructsPartsFromPathWithSlug() {
    $this->repository->setContext('FoodCategories');

    $this->assertEquals(
      '/food/fruits/citrons',
      $this->repository->getUrl($this->hydrateNode('citrons'), $this->defaultLanguage)
    );
  }
}
