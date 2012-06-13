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
  
  public function testConstruct_emptyArguments() {
    $package = new DCPackage();
    
    $this->assertInstanceOf('Doctrine\ORM\EntityManager', $package->getEntityManager());
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