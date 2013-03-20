<?php

namespace Psc\Doctrine;

use Webforge\TestData\NestedSet\NestedSetExample;
use Doctrine\Common\Persistence\ObjectManager;

class NestedSetsFixture extends Fixture {

  /**
   * @var list(Webforge\TestData\NestedSet\NestedSetExample $example, string $context)
   */
  protected $examples;

  /**
   *  list($example, $context)[]
   */
  public function __construct(Array $exampleContexts) {
    $this->examples = $exampleContexts;
  }

  /**
   * Load data fixtures with the passed EntityManager
   * 
   * @param Doctrine\Common\Persistence\ObjectManager $manager
   */
  public function load(ObjectManager $em) {
    foreach ($this->examples as $list) {
      list($example, $context) = $list;

      $fixture = new NestedSetFixture($example, $context);
      $fixture->load($em);
    }
  }
}
?>