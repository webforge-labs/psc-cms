<?php

namespace Psc\Graph;

/**
 * @group class:Psc\Graph\DependencyVertice
 */
class DependencyVerticeTest extends \Psc\Code\Test\Base {
  
  protected $vertice;
  
  public function setUp() {
    $this->chainClass = 'Psc\Graph\DependencyVertice';
    parent::setUp();
    $this->vertice = new DependencyVertice('defaultVerticeLabel');
  }
  
  public function testIsVisitedIsFalsePerDefault() {
    $this->assertFalse($this->vertice->isVisited());
  }
  
  public function testIsVisitedReturnsVisited() {    
    $this->vertice->setVisited();
    $this->assertTrue($this->vertice->isVisited());
  }
}
?>