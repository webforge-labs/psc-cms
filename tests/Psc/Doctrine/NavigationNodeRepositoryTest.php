<?php

namespace Psc\Doctrine;

class NavigationNodeRepositoryTest extends \Psc\Doctrine\RepositoryTest {
  
  public function setUp() {
    $this->chainClass = 'Psc\\Doctrine\\NavigationNodeRepository';
    $this->entityClass = 'Psc\Entities\NavigationNode';

    $this->fixtures = array(
      new NestedSetsFixture(self::getFixtures())
    );

    parent::setUp();
  }

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

  protected function countFixture($fixture) {
    return count($fixture->toArray());
  }


  public static function getFixtures() {
    return Array(
      array(new \Webforge\TestData\NestedSet\FoodCategories(), 'FoodCategories'),
      array(new \Webforge\TestData\NestedSet\Consumables(), 'Consumables')
    );
  }
  
}
?>