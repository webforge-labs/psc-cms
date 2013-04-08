<?php

namespace Psc\Entities;

use Psc\Entities\ContentStream\ContentStream;

class NavigationNodeTest extends \Psc\Code\Test\Base {
  
  protected $navigationNode;
  
  public function setUp() {
    $this->chainClass = 'Psc\Entities\NavigationNode';
    parent::setUp();
    $this->navigationNode = new NavigationNode(array('de'=>'something de', 'fr'=>'something in france'));

    $this->cs1 = ContentStream::create('de', 'page-content', 'default', 'de-default');
    $this->cs2 = ContentStream::create('en', 'page-content', 'default', 'en-default');
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

  public function testCOntextLoadableIsImplementedCorrectly() {
    $controller = $this->getMock('Psc\CMS\Controller\NavigationController', array(), array(), '', FALSE);
    $context = $this->getMockForAbstractClass('Psc\TPL\ContentStream\Context');
    $context->expects($this->once())->method('getNavigationController')->will($this->returnValue($controller));

    $id = 7;
    $this->navigationNode->setIdentifier($id);

    $controller->expects($this->once())->method('getEntity')
      ->with($this->equalTo($id))
      ->will($this->returnValue($this->navigationNode));

    $navNode = NavigationNode::loadWithContentStreamContext($id, $context);

    $this->assertSame($this->navigationNode, $navNode);
  }

  public function testContextLoadableReturnsNULLWhenIdenfieristBullshit() {
    $context = $this->getMockForAbstractClass('Psc\TPL\ContentStream\Context');

    $context->expects($this->never())->method('getNavigationController');

    $this->assertNull(NavigationNode::loadWithContentStreamContext("0", $context));
  }

  public function testHasContentStreamEntities() {
    $this->assertInstanceOf('Psc\TPL\ContentStream\Collection', $this->navigationNode->getContentStream());
  }

  protected function expectControllerInContext() {
    return array($controller, $context);
  }
}
