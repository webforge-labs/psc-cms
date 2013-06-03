<?php

namespace Psc\Code\Test;

use Psc\Code\Test\DoublesManager;
use Psc\Net\HTTP\Request;

/**
 * @group class:Psc\Code\Test\DoublesManager
 */
class DoublesManagerTest extends \Psc\Code\Test\Base {
  
  protected $manager;
  
  public function setUp() {
    $this->manager = new DoublesManager($this);
  }

  public function testCreateHTTPRequest() {
    $manager = $this->manager;
    
    $request = $manager->createHTTPRequest('GET', '/js/cms/ui.comboBox.js');
    $this->assertInstanceof('Psc\Net\HTTP\Request', $request);
    $this->assertEquals(Request::GET, $request->getMethod());
    $this->assertNotEmpty($request->getHeader()->getField('User-Agent'),' User Agent nicht gesetzt');
    
    $request = $manager->createHTTPRequest('POST', '/some/form.php');
    $this->assertInstanceof('Psc\Net\HTTP\Request', $request);
    $this->assertEquals(Request::POST, $request->getMethod());
    $this->assertNotEmpty($request->getHeader()->getField('User-Agent'),' User Agent nicht gesetzt');
    
    // not yet ready (so geht noch nicht)
  }
  
  public function testCreateEntityManagerMock() {
    $this->assertInstanceOf('Doctrine\ORM\EntityManager', $this->manager->createEntityManagerMock());
  }
  
  public function testCreateDoctrinePackageMock() {
    $this->assertInstanceOf('Psc\Doctrine\DCPackage', $dc = $this->manager->createDoctrinePackageMock());
    $this->assertInstanceOf('Doctrine\ORM\EntityManager', $dc->getEntityManager());
    $this->assertInstanceOf('Psc\Doctrine\Module', $dc->getModule());
  }
  
  public function testCreateFileMock() {
    $this->assertInstanceOf('Webforge\Common\System\File',$this->manager->createFileMock());
  }
  
  public function testCreatePDOUniqueConstraintException() {
    $this->assertInstanceOf('PDOException', $e = $this->manager->createPDOUniqueConstraintException('my_unique_constraint'));
    $this->assertInstanceOf('Psc\Doctrine\UniqueConstraintException', $dce = \Psc\Doctrine\Exception::convertPDOException($e));
    $this->assertEquals('my_unique_constraint',$dce->uniqueConstraint);
  }
}
?>