<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\EntityViewPackage
 */
class EntityViewPackageTest extends \Psc\Code\Test\Base {
  
  protected $ev;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntityViewPackage';
    parent::setUp();
    $this->ev = new EntityViewPackage();
  }
  
  public function testDPIGeneration() {
    $this->assertInstanceOf('Psc\CMS\ComponentMapper',$this->ev->getComponentMapper());
    $this->assertInstanceOf('Psc\CMS\Labeler',$this->ev->getLabeler());
  }
}
?>