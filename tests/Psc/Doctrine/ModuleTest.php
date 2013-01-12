<?php

namespace Psc\Doctrine;

use Psc\Doctrine\Module;
use Psc\PSC;

/**
 * @group class:Psc\Doctrine\Module
 */
class ModuleTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->module = clone $this->getProject()->getModule('Doctrine');
  }

  /**
   * @dataProvider connectionNames
   */
  public function testModuleGetEntityManagerReturnsADoctrineEntityManagerForConnectionName($con) {
    $this->module->setConnectionName($con);
    
    $em = $this->module->getEntityManager();
    $this->assertInstanceof('Doctrine\ORM\EntityManager', $em);
    
    $this->assertEquals(
      $this->getProject()->getConfiguration()->req('db.'.$con.'.database'),
      $em->getConnection()->getDatabase()
    );
  }
  
  /**
   * @dataProvider connectionNames
   */
  public function testModuleEntityManagerReturnsConnectionNameManagerPerDefault($con) {
    $this->module->setConnectionName($con);
    $this->assertSame(
      $this->module->getEntityManager(),
      $this->module->getEntityManager($con)
    );
  }
    
  public static function connectionNames() {
    return Array(
      array('default'),
      array('tests')
    );
  }
  
  public function testEntityManagerInstancesReusingConnection() {
    $em = $this->module->getEntityManager('default');
    $this->assertInstanceof('Doctrine\ORM\EntityManager',$em);
    
    $connection = $em->getConnection();
    $newEM = $this->module->getEntityManager('default', $reset = TRUE);
    
    $this->assertNotSame($em, $newEM);
    $this->assertSame($em->getConnection(), $newEM->getConnection(), 'Module benutzt die alte Connection nicht wieder!');
  }
  
  /**
   * @covers Psc\Doctrine\MetadataDriver::getAllClassNames
   * @covers Psc\Doctrine\MetadataDriver::addClass
   */
  public function testEntityMetadataDriver_getAllClassNames() {
    $driver = clone $this->module->getEntityClassesMetadataDriver();
    
    $driver->addClass('Some\Entities\Test');
    
    $this->assertContains(
      'Some\Entities\Test',
      $driver->getAllClassNames()
    );
    
    return $driver;
  }
  
  /**
   * @depends testEntityMetadataDriver_getAllClassNames
   * @covers Psc\Doctrine\MetadataDriver::removeClass
   * @covers Psc\Doctrine\MetadataDriver::getAllClassNames
   */
  public function testEntityMetadataDriver_removeClassName($driver) {
    $driver->removeClass('\Some\Entities\Test');
    
    $this->assertNotContains(
      'Some\Entities\Test',
      $driver->getAllClassNames()
    );
  }
  
  public function testEntityLoadingAcceptance() {
    $em = $this->module->getEntityManager('default');
    
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