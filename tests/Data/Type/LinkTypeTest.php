<?php

namespace Psc\Data\Type;

use Psc\Data\Type\LinkType;

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
    $this->assertInstanceOf('Psc\Data\Type\URIType',$link->getComponent(1));
    $this->assertInstanceOf('Psc\Data\Type\StringType',$link->getComponent(2));
  }
  
  /**
   * @depends testConstruct
   */
  public function testInterfaced($link) {
    $this->assertInstanceOf('Psc\Data\Type\InterfacedType', $link);
    $this->assertTrue(interface_exists($link->getInterface()), 'Interface: '.$link->getInterface().' existiert nicht');
    $this->assertEquals('Psc\Data\Type\Interfaces\Link', $link->getInterface());
    $this->assertEquals('Psc\Data\Type\Interfaces\Link', $link->getPHPHint());
  }
}
?>