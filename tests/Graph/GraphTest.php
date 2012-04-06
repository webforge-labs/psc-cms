<?php

use \Psc\Graph\Graph,
    \Psc\Graph\Vertice as GraphVertice,
    \Psc\Graph\Edges as GraphEdges,
    \Psc\Graph\Edge as GraphEdge,
    \Psc\Graph\BFSVertice as GraphBFSVertice,
    \Psc\Graph\ColorVertice as GraphColorVertice,
    \Psc\Graph\Alg as GraphAlg
;

class GraphTest extends PHPUnit_Framework_TestCase {
  
  public function testConstruct() {
    $graph = new Graph();
    
    $this->assertInstanceOf('\Psc\Graph\Edges', $graph->E());
    return $graph;
  }
    
  /**
   * @depends testConstruct
   */
  public function testAdd(Graph $graph) {
    $v = new GraphVertice(1);
    $u = new GraphVertice(2);
    
    $graph->add($u); // neue Vertice
    $graph->add($v); // neue Vertice
    
    $this->assertTrue($graph->has($u));
    $this->assertTrue($graph->has($v));

    $graph->add($u, $v); // neue Kante
    
    $this->assertTrue($graph->has($u, $v));
    
    $h = new Graph();
    $h->add($u,$v);
    
    $this->assertTrue($h->has($u));
    $this->assertTrue($h->has($v));
    $this->assertTrue($h->has($u,$v));
    
    $this->assertFalse($h->has($v,$u)); // solange wir keinen ungerichteten Graphen haben muss das hier false sein
  }
  
  public function testBFS1() {
    $graph = new Graph();
    
    /* Beispiel Graph aus Cormen Seite 537 */
    foreach (array('r','s','t','u','v','w','x','y') as $char) {
      $$char = new GraphBFSVertice($char);
    }
    
    $graph->add($r,$v);
    $graph->add($r,$s);
    
    $graph->add($s,$w);
    $graph->add($s,$r);
    
    $graph->add($t,$w);
    $graph->add($t,$x);
    $graph->add($t,$u);
    
    $graph->add($u,$x);
    $graph->add($u,$y);
    $graph->add($u,$t);
    
    $graph->add($v,$r);
    
    $graph->add($w,$s);
    $graph->add($w,$t);
    $graph->add($w,$x);

    $graph->add($x,$w);
    $graph->add($x,$t);
    $graph->add($x,$u);
    $graph->add($x,$y);
    
    $graph->add($y,$u);
    $graph->add($y,$x);
    
    GraphAlg::BFS($graph, $s);
    
    /* jetzt wirds lustig */
    foreach (Array(
      'r'=>1,
      's'=>0,
      't'=>2,
      'u'=>3,
      'v'=>2,
      'w'=>1,
      'x'=>2,
      'y'=>3,
      ) as $char => $distance) {
      
      $this->assertEquals($distance, $$char->d, 'distance von '.$char.' ist falsch');
      $this->assertEquals('schwarz', $$char->color);
      
      /* TODO: die referencen in E und V sollten gleich sein! */
      
      //foreach($graph->E()->getLists() as $list) {
//  foreach ($list as $v) {
//    print $v->label.': #'.SimpleObject::getObjectId($v)."\n";
//  }
//}
//print "\n";
//
//foreach($graph->V() as $v) {
//  print $v->label.': #'.SimpleObject::getObjectId($v)."\n";
//}

        
    }
  }
}
?>