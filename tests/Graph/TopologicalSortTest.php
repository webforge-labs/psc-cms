<?php

namespace Psc\Graph;

/**
 * @group class:Psc\Graph\Alg
 */
class TopologicalSortTest extends \Psc\Code\Test\Base {
  
  protected $graph;
  
  public function setUp() {
    $this->chainClass = 'Psc\Graph\Alg';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $graph = new Graph();
    
    // erstelle alle knoten
    foreach ($l = array('php','apache','website','pear','phpUnit','phpFramework','database','c') as $vertice) {
      $$vertice = new DependencyVertice($vertice);
      $$vertice->setContextGraph($graph);
    }
    
    // eine topologische Sortierung eines DAG ist eine Lineare Anordnung all seiner Knoten mit der Eigenschaft
    // dass u in der Anordnung vor v liegt (v ist abhängig von u), falls es eine Kante (u,v) gibt

    $php->dependsOn($c); // macht nichts anderes als $graph->add($c, $php);
    $phpFramework->dependsOn($php);
    $phpFramework->dependsOn($phpUnit);
    
    $phpUnit->dependsOn($pear);
    $pear->dependsOn($php);
    
    $website->dependsOn($phpFramework);
    $website->dependsOn($database);
    $website->dependsOn($apache);
    
    $apache->dependsOn($c);
    
    $list = Alg::TopologicalSort($graph);
    
    // alle Knoten müssen in der Order vorkommen
    $this->assertEquals(count($l), count($list));
    
    $that = $this;
    $assert = function ($a, $where, $b) use ($that, $list) {
      $that->assertComesBefore($list, $a, $b);
    };
    
    //print "\n".implode(", ", $list);
    $assert($c, 'comesBefore', $php);
    $assert($c, 'comesBefore', $apache);
    $assert($c, 'comesBefore', $phpFramework);
    $assert($c, 'comesBefore', $website);
    $assert($php, 'comesBefore', $phpFramework);
    $assert($php, 'comesBefore', $pear);
    $assert($php, 'comesBefore', $phpUnit);
    $assert($php, 'comesBefore', $website);
    $assert($pear, 'comesBefore', $phpUnit);
    $assert($phpUnit, 'comesBefore', $website);
    $assert($apache, 'comesBefore', $website);
    $assert($database, 'comesBefore', $website);
    $assert($phpFramework, 'comesBefore', $website);
    
    
  }
  
  /**
   * @expectedException \Psc\Graph\TopologicalSortRuntimeException
   */
  public function testWithCyclicDependencies() {
    $graph = new Graph();
    
    $v1 = new DependencyVertice('v1');
    $v1->setContextGraph($graph);
    
    $v2 = new DependencyVertice('v2');
    $v2->setContextGraph($graph);
    
    $v3 = new DependencyVertice('v3');
    $v3->setContextGraph($graph);
    
    $v1->dependsOn($v2);
    $v2->dependsOn($v3);
    $v3->dependsOn($v1);
    
    $list = Alg::TopologicalSort($graph);
  }
  
  public function assertComesBefore($list, $a, $b) {
    // nicht schön und schnell, aber verständlich
    $indexA = array_search($a, $list, TRUE);
    $indexB = array_search($b, $list, TRUE);
    
    if ($indexA === FALSE || $indexB === FALSE) {
      throw new \RuntimeException('$a oder $b wurden im graph nicht gefunden, das ist sehr komisch!');
    }
    
    $this->assertLessThan($indexB, $indexA, $a.' liegt nicht vor '.$b.' in der liste!');
  }
}
?>