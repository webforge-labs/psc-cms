<?php

namespace Psc\Doctrine;

/**
 * @group class:Psc\Doctrine\Package
 */
class PackageTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\Package';
    parent::setUp();
  }
  
  public function testConstruct_withoutEntityManager() {
    $package = new DCPackage($this->getModule('Doctrine'));
    
    $this->assertInstanceOf('Doctrine\ORM\EntityManager', $em = $package->getEntityManager());
    $this->assertInstanceOf('Psc\Doctrine\Module', $package->getModule());
  }
  
  public function testConstruct_DPI_Constructor() {
    $module = new Module(\Psc\PSC::getProject());
    $em = $this->doublesManager->createEntityManagerMock();
    
    $package = new DCPackage($module, $em);
    $this->assertSame($em, $package->getEntityManager());
    $this->assertSame($module, $package->getModule());
  }
}
?>