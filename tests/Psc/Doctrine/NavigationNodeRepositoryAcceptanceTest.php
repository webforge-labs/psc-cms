<?php

namespace Psc\Doctrine;

use Psc\Code\Code;
use Webforge\CMS\Navigation\DoctrineBridge;
use Webforge\CMS\Navigation\NestedSetConverter;
use Psc\Entities\NavigationNode;

class NavigationNodeRepositoryAcceptanceTest extends \Psc\Test\DatabaseTestCase {

  public function setUp() {
    parent::setUp();

    $this->converter = new NestedSetConverter();

    $bridge = new DoctrineBridge($this->em);
    $bridge->beginTransaction();
    $this->context = 'footer';

    $start = $bridge->persist($this->createNode('Start'));
    $bridge->persist($this->createNode('Suche', $start));
    $bridge->persist($this->createNode('Newsletter', $start));
    $bridge->persist($this->createNode('Forum', $start));

    $bridge->commit();


    $bridge = new DoctrineBridge($this->em);
    $bridge->beginTransaction();
    $this->context = 'meta';

    $start = $bridge->persist($this->createNode('Start'));
    $bridge->persist($this->createNode('Impressum', $start));
    $bridge->persist($this->createNode('Kontakt', $start));
    $bridge->persist($this->createNode('Datenschutz', $start));

    $bridge->commit();

    $bridge = new DoctrineBridge($this->em);
    $bridge->beginTransaction();
    $this->context = 'default';

    $start = $bridge->persist($this->createNode('Start'));
    $bridge->persist($this->createNode('Über Uns', $start));
    $bridge->persist($this->createNode('Produkte', $start));
    $bridge->persist($this->createNode('Service', $start));

    $bridge->commit();

    $this->em->flush();

    $this->repository = $this->em->getRepository('Psc\Entities\NavigationNode');
  }

  protected function createNode($title, $parent = NULL) {
    $node = new NavigationNode(array('de'=>$title));
    $node->setContext($this->context);

    if ($parent) {
      $node->setParent($parent);
    }

    return $node;
  }
  
  public function testContextMergingOfNodes() {
    $mergedNodes = $this->repository->mergedChildrenQueryBuilder()->getQuery()->getResult();

    $text = $this->converter->toString($mergedNodes);

    $this->assertEquals(
      $expectedText = <<<'TEXT'
Start
  Über Uns
  Produkte
  Service
  Suche
  Newsletter
  Forum
  Impressum
  Kontakt
  Datenschutz

TEXT
,
      $text, 
      "expected >>>\n".$expectedText."\n\n".
      "actual >>>\n".$text."\n"
    );
  }
}
