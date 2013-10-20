<?php

namespace Psc\Data\Type;

use Psc\Data\Type\LinkType;

/**
 * @group class:Psc\Data\Type\LinkType
 */
class LinkTypeTest extends CompositeTypeTestCase {
  
  public function setUp() {
    $this->typeName = 'Link';
  }
  
  public function testConstruct() {
    return parent::testConstruct();
  }
  
  /**
   * @depends testConstruct
   */
  public function testComponentsOrder($link) {
    $this->assertInstanceOf('Webforge\Types\URIType',$link->getComponent(1));
    $this->assertInstanceOf('Webforge\Types\StringType',$link->getComponent(2));
  }
  
  /**
   * @depends testConstruct
   */
  public function testInterfaced($link) {
    $this->assertInstanceOf('Webforge\Types\InterfacedType', $link);
    $this->assertTrue(interface_exists($link->getInterface()), 'Interface: '.$link->getInterface().' existiert nicht');
    $this->assertEquals('Webforge\Types\Interfaces\Link', $link->getInterface());
    $this->assertEquals('Webforge\Types\Interfaces\Link', $link->getPHPHint());
  }
}
?>