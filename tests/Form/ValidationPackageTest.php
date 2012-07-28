<?php

namespace Psc\Form;

/**
 * @group class:Psc\Form\ValidationPackage
 */
class ValidationPackageTest extends \Psc\Code\Test\Base {
  
  protected $package;
  
  public function setUp() {
    $this->chainClass = 'Psc\Form\ValidationPackage';
    parent::setUp();
    $this->package = new ValidationPackage;
  }

  public function testCheckingCapturesExceptions() {
    $id = $hash = NULL;
    $idOrHash = 7;
    
    $this->assertTrue($this->package->check(function ($v) use ($idOrHash, &$id) {
      $id = $v->validateId($idOrHash);
    }));

    $idOrHash = 'blubb';
    $this->assertFalse($this->package->check(function ($v) use ($idOrHash, &$id) {
      $id = $v->validateId($idOrHash);
    }));
  }
  
  /**
   * @dataProvider provideIdentifiers
   */
  public function testValidateId($expectedIdentifier, $identifier) {
    $this->assertEquals($expectedIdentifier, $this->package->validateId($identifier));
  }
  
  /**
   * @dataProvider provideBadIdentifiers
   * @expectedException Psc\Exception
   */
  public function testValidateBadId($identifier) {
    $this->package->validateId($identifier);
  }
  
  public static function provideIdentifiers() {
    return Array(
      array(7, '7'),
      array(7, ' 7 ')
    );
  }
  
  public static function provideBadIdentifiers() {
    return Array(
      array(0),
      array('abc'),
      array(-7),
      array(array())
    );
  }
}
?>