<?php

namespace Psc\Entities;

class NavigationNodeTest extends \Psc\Code\Test\Base {
  
  protected $navigationNode;
  
  public function setUp() {
    $this->chainClass = 'Psc\Entities\NavigationNode';
    parent::setUp();
    $this->navigationNode = new NavigationNode(array('de'=>'something de', 'fr'=>'something in france'));
  }
  
  public function testNewNavigationNodeDoesNotNodeEqualOtherNewNavigationNode() {
    $other = new NavigationNode(array('de'=>'something other de', 'fr'=>'something other in france'));
    $this->assertFalse($this->navigationNode->equalsNode($other), 'new node should not equal new other node');
    
    $this->assertTrue($this->navigationNode->equalsNode($this->navigationNode), 'navigationNode should equal itself');
  }
  
  public function testConstructorHasI18nTitle() {
    $this->assertEquals('something de', $this->navigationNode->getTitle('de'));
    $this->assertEquals('something in france', $this->navigationNode->getTitle('fr'));
  }
}
