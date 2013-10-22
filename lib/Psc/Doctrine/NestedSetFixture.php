<?php

namespace Psc\Doctrine;

use Webforge\TestData\NestedSet\NestedSetExample;
use Webforge\Doctrine\CMS\NavigationBridge as DoctrineBridge;
use Psc\Code\Code;

use Psc\Entities\NavigationNode;
use Psc\Entities\Page;
use Doctrine\Common\Persistence\ObjectManager;

class NestedSetFixture extends Fixture {

  /**
   * @var Webforge\TestData\NestedSet\NestedSetExample
   */
  protected $example;

  public function __construct(NestedSetExample $example, $context = 'default') {
    $this->example = $example;
    $this->context = $context;
  }

  /**
   * Load data fixtures with the passed EntityManager
   * 
   * @param Doctrine\Common\Persistence\ObjectManager $manager
   */
  public function load(ObjectManager $em) {
    $bridge = new DoctrineBridge($em);
    $bridge->beginTransaction();

    $nodes = $this->convertNodes($this->example->toParentPointerArray(), $em);

    foreach($nodes as $node) {
      $bridge->persist($node);
    }

    $bridge->commit();
    $em->flush();
  }

  /**
   * we elevate every node to an entity and set parent pointers
   */
  protected function convertNodes(Array $nodes, $em) {
    // our test project (this) is defined to have only de as i18n but dont get confused, these are english wordings
    $navigationNodes = array();

    foreach ($nodes as $node) {
      $node = (object) $node;
      $navigationNode = $this->createNode(array('de'=>$node->title));
      $navigationNode->setContext($this->context);
      $navigationNode->generateSlugs();

      $page = $this->createPage($navigationNode->getSlug('de'));
      $page->setActive(TRUE);
      $em->persist($page);

      $navigationNode->setPage($page);

      $navigationNodes[$navigationNode->getTitle('de')] = $navigationNode;
    }

    foreach ($nodes as $node) {
      $node = (object) $node;

      if (isset($node->parent)) {
        $navigationNodes[$node->title]->setParent($navigationNodes[$node->parent]);
      }
    }

    return $navigationNodes;
  }

  protected function createNode(Array $i18nTitle) {
    $navigationNode = new NavigationNode($i18nTitle);

    return $navigationNode;
  }

  protected function createPage($slug) {
    return new Page($slug);
  }
}
