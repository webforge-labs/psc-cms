<?php

namespace Psc\Doctrine;

use Psc\Doctrine\Module;
use Psc\PSC;

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
}
?>