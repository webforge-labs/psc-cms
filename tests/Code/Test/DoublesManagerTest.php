<?php

namespace Psc\Code\Test;

use Psc\Code\Test\DoublesManager;
use Psc\Net\HTTP\Request;

class DoublesManagerTest extends \Psc\Code\Test\Base {

  public function testCreateHTTPRequest() {
    $manager = $this->createManager();
    
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
  
  //public function testCreateTestCaseMock() {
  //  $testCase = $this->createManager()->createTestCaseMock();
  //  $this->assertInstanceOf('PHPUnit_Framework_TestCase', $testCase);
  //  $testCase->assertEquals('nein','ja');
  //}
  
  public function testCreateTestCase() {
    $this->assertInstanceOf('Psc\Code\Test\ClosureTestCase',$testCase = $this->createManager()->createClosureTestCase(function () {
    }));
  }
  
  public function testCreateEntityManagerMock() {
    $this->assertInstanceOf('Doctrine\ORM\EntityManager', $this->createManager()->createEntityManagerMock());
  }
  
  public function testGetFileMock() {
    $this->assertInstanceOf('Psc\System\File',$this->createManager()->createFileMock());
  }
  
  public function testCreatePDOUniqueConstraintException() {
    $this->assertInstanceOf('PDOException', $e = $this->createManager()->createPDOUniqueConstraintException('my_unique_constraint'));
    $this->assertInstanceOf('Psc\Doctrine\UniqueConstraintException', $dce = \Psc\Doctrine\Exception::convertPDOException($e));
    $this->assertEquals('my_unique_constraint',$dce->uniqueConstraint);
  }
  
  protected function createManager() {
    return new DoublesManager($this);
  }
}
?>