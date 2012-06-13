<?php

namespace Psc\Gedmo;

use Psc\CMS\Navigation\Node as NavigationNode; // auch in getRepository ändern
use Psc\CMS\Navigation\NodeTranslation as NodeTranslation; 

/**
 * @group class:Psc\Gedmo\Module
 */
class ModuleTest extends \Psc\Doctrine\DatabaseTest {
  
  protected $gedmo;
  
  public function setUp() {
    $this->con = 'tests';
    $this->chainClass = 'Psc\Gedmo\Module';
    parent::setUp();
    $this->gedmo = new Module(\Psc\PSC::getProject()); //->getModule('Gedmo');
    $this->gedmo->setDoctrineModule($this->module);
    $this->gedmo->bootstrap();
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Doctrine\ORM\EntityManager', $em = $this->gedmo->getEntityManager($this->con));
    $repository = $em->getRepository('Psc\CMS\Navigation\Node');
    
    $this->updateSchema();
  }
  
  public function testInitDoesAddListenersOnlyOnce() {
    $evm = $this->em->getEventManager();
    
    $this->gedmo->initEventManager($evm);
    $this->assertTrue($evm->hasListeners('onFlush'));
    $listeners = count($evm->getListeners('onFlush'));
    
    $this->gedmo->initEventManager($evm);
    $this->assertEquals($listeners, count($evm->getListeners('onFlush')));
  }
  
  public function testExample() {
    // acceptance wird bereits in navigation* getestet
    return; 
    
    $this->truncateTable(array('navigation_nodes', 'navigation_node_translations'));

    $this->gedmo->initEventManager($this->em->getEventManager());
    $em = $this->em;
    
    $repository = $em->getRepository('Psc\CMS\Navigation\Node');
        $food = new NavigationNode;
        $food->setTitle('Food');
        $food->addTranslation(new NodeTranslation('lt', 'title', 'Maistas'));
    
        $fruits = new NavigationNode;
        $fruits->setParent($food);
        $fruits->setTitle('Fruits');
        $fruits->addTranslation(new NodeTranslation('lt', 'title', 'Vaisiai'));
    
        $apple = new NavigationNode;
        $apple->setParent($fruits);
        $apple->setTitle('Apple');
        $apple->addTranslation(new NodeTranslation('lt', 'title', 'Obuolys'));
    
        $milk = new NavigationNode;
        $milk->setParent($food);
        $milk->setTitle('Milk');
        $milk->addTranslation(new NodeTranslation('lt', 'title', 'Pienas'));
    
        $em->persist($food);
        $em->persist($milk);
        $em->persist($fruits);
        $em->persist($apple);
        $em->flush();

    $that = $this;
    $treeDecorationOptions = array(
        'decorate' => true,
        'rootOpen' => '',
        'rootClose' => '',
        'childOpen' => '',
        'childClose' => '',
        'nodeDecorator' => function($node) use ($that) {
          return str_repeat('-', $node['level']).$node['title']."\n";
        }
    );
    
    // build tree in english
    $this->assertEquals("Food\n-Milk\n-Fruits\n--Apple\n", $repository->childrenHierarchy(null, false, $treeDecorationOptions));


     // create query to fetch tree nodes
    $query = $em
        ->createQueryBuilder()
        ->select('node')
        ->from('\Psc\CMS\Navigation\Node', 'node')
        ->orderBy('node.root, node.lft', 'ASC')
        ->getQuery()
    ;
    // set hint to translate nodes
    $query->setHint(
        \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
        'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
    );
    
    $this->dump($query->getResult());
  }
}
?>