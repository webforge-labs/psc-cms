<?php

namespace Psc\Doctrine;

use Psc\Code\Code;

class NavigationNodesTestCase extends \Psc\Doctrine\RepositoryTest {
  
  public function setUp() {
    $this->chainClass = 'Psc\\Doctrine\\NavigationNodeRepository';
    $this->entityClass = 'Psc\Entities\NavigationNode';

    $this->fixtures = array(
      new NestedSetsFixture(self::getFixtures())
    );

    parent::setUp();

    $this->defaultLanguage = 'de';
    $this->languages = array('de');
  }

  protected function hydrateNode($slug) {
    return $this->hydrate($this->entityClass, array('slugDe'=>$slug));
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

  protected function createRepository(Array $methods = NULL) {
    return $this->em->getRepository($this->entityClass);
  }
}
