<?php

namespace Psc\CMS\Navigation;

/**
 * @group class:Psc\CMS\Navigation\Node
 */
class NodeTest extends \Psc\Code\Test\Base {
  
  protected $node;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Navigation\Node';
    parent::setUp();
    $this->node = $this->getMockForAbstractClass('Psc\CMS\Navigation\Node');
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Doctrine\Common\Collections\Collection', $this->node->getTranslations());
    $this->assertInstanceOf('Doctrine\Common\Collections\Collection', $this->node->getChildren());
  }
}
?>