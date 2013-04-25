<?php

namespace Psc\Doctrine;

use Webforge\CMS\Navigation\NestedSetConverter;
use Webforge\CMS\Navigation\Node;
use Webforge\Common\ArrayUtil as A;

class NavigationView {

  protected $converter;

  protected $repository;

  protected $root;

  protected $paths = array();

  protected $urls = array();

  public function __construct(NavigationNodeRepository $repository) {
    $this->repository = $repository;
    $this->converter = new NestedSetConverter();
  }

  public function loadAll() {
    $nodes = $this->repository->childrenQueryBuilder(
      NULL,
      $this->repository->getDefaultQueryBuilderHook(function ($qb) {
        $qb->addSelect('page');

      })
    )->getQuery()->getResult();

    $paths = array();
    $tree = $this->converter->toStructure($nodes, array(
      'onNodeComplete' => function (Node $node, Node $parent = NULL, Array $stack) use (&$paths) {
        array_shift($stack);
        $paths[$node->getIdentifier()] = $stack;
      }
    ));

    $this->paths = $paths;
    $this->root = $tree[0];
  }

  /**
   * @return string
   */
  public function getUrl(Node $node, $locale) {
    if (array_key_exists($node->getIdentifier(), $this->urls)) {
      return $this->urls[$node->getIdentifier()];
    } else {
      return $this->urls[$node->getIdentifier()] = $this->repository->createUrl($this->getPath($node), $locale);
    }
  }

  /**
   * @return array
   */
  public function getPath(Node $node) {
    if (array_key_exists($node->getIdentifier(), $this->paths)) {
      return $this->paths[$node->getIdentifier()];
    } else {
      return $this->paths[$node->getIdentifier()] = $this->repository->getPath($node);
    }
  }

  public function getRoot() {
    return $this->root;
  }
}
