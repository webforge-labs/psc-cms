<?php

namespace Psc\Doctrine;

use Psc\Doctrine\Module;
use Psc\PSC;

/**
 * @group class:Psc\Doctrine\Module
 */
class ModuleTest extends \Psc\Code\Test\Base {

  public function testEnityManagersInstances() {
    $module = clone PSC::getProject()->getModule('Doctrine');
    // wenn wir hier nicht clonen hat das seiteneffekte auf die anderen tests
    // voralem die in PSC, global testen ist halt echt ne doofe idee von mir
    
    $conf = PSC::getProject()->getConfiguration();
    $defaultDB = 'psc-cms';
    $testsDB = 'psc-cms_tests';

    $emTests = $module->getEntityManager();
    $this->assertInstanceof('Doctrine\ORM\EntityManager',$emTests);
    $this->assertEquals($testsDB, $emTests->getConnection()->getDatabase());
    
    $module->setConnectionName('default');
    $emDefault = $module->getEntityManager();
    $this->assertInstanceof('Doctrine\ORM\EntityManager',$emDefault);
    $this->assertEquals($defaultDB, $emDefault->getConnection()->getDatabase());
    
    $this->assertNotEquals($defaultDB,$testsDB);
    
    $emTests2 = $module->getEntityManager('tests');
    $this->assertSame($emTests2,$emTests);

    $emDefault2 = $module->getEntityManager('default');
    $this->assertSame($emDefault2,$emDefault);
  }
  
  public function testEntityManagerInstancesReusingConnection() {
    $module = clone PSC::getProject()->getModule('Doctrine');
    
    $em = $module->getEntityManager('default');
    $this->assertInstanceof('Doctrine\ORM\EntityManager',$em);
    
    $connection = $em->getConnection();
    $newEM = $module->getEntityManager('default', $reset = TRUE);
    
    $this->assertNotSame($em, $newEM);
    $this->assertSame($em->getConnection(), $newEM->getConnection(), 'Module benutzt die alte Connection nicht wieder!');
  }
  
  /**
   * @covers Psc\Doctrine\MetadataDriver::getAllClassNames
   * @covers Psc\Doctrine\MetadataDriver::addClass
   */
  public function testEntityMetadataDriver_getAllClassNames() {
    $module = PSC::getProject()->getModule('Doctrine');
    $driver = clone $module->getEntityClassesMetadataDriver();
    
    $driver->addClass('Some\Entities\Test');
    $this->assertTrue(in_array('Some\Entities\Test',$driver->getAllClassNames()), 'Some\Entities\Test ist nicht in '.print_r($driver->getAllClassNames(),true));
    return $driver;
  }
  
  /**
   * @depends testEntityMetadataDriver_getAllClassNames
   * @covers Psc\Doctrine\MetadataDriver::removeClass
   * @covers Psc\Doctrine\MetadataDriver::getAllClassNames
   */
  public function testEntityMetadataDriver_removeClassName($driver) {
    $driver->removeClass('\Some\Entities\Test');
    
    $this->assertFalse(in_array('Some\Entities\Test',$driver->getAllClassNames()), 'Some\Entities\Test ist immer noch in getAllClassNames()');
  }
  
  public function testEntityLoadingAcceptance() {
    $module = PSC::getProject()->getModule('Doctrine');
    $em = $module->getEntityManager('default');
    
    // das sind künstliche Entities die Entities aus der Library ableiten
    // BasicImage
    // BasicPerson
    // User etc
    $this->assertInstanceOf('Doctrine\ORM\EntityRepository', $em->getRepository('Psc\Entities\Image'));
    $this->assertInstanceOf('Doctrine\ORM\EntityRepository', $em->getRepository('Psc\Entities\User'));
    $this->assertInstanceOf('Doctrine\ORM\EntityRepository', $em->getRepository('Psc\Entities\Person'));
  }
}
?>