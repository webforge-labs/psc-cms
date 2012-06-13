<?php

namespace Psc;

/**
 * @group class:Psc\Inflector
 */
class InflectorTest extends \Psc\Code\Test\Base {
  
  protected $inflector;
  
  public function setUp() {
    $this->chainClass = 'Psc\Inflector';
    parent::setUp();
    $this->inflector = new Inflector();
  }
  
  /**
   * @dataProvider providePropertyNames
   */
  public function testPropertyName($expectedName, $className) {
    $this->assertEquals($expectedName, $this->inflector->propertyName($className));
  }
  
  public static function providePropertyNames() {
    $tests = array();
    
    $tests[] = array('oid', 'OID');
    $tests[] = array('oidMeta', 'OIDMeta');
    $tests[] = array('someClassName', 'SomeClassName');
    
    
    return $tests;
  }

  /**
   * @dataProvider provideBadPropertyNames
   */
  public function testBadPropertyName($className, $exception = 'RuntimeException') {
    $this->setExpectedException($exception);
    $this->inflector->propertyName($className);
  }
  
  public static function provideBadPropertyNames() {
    $tests = array();
    
    // edge weil eigentlich falsche konvention
    $tests[] = array('wrongNamedClass');
    
    $tests[] = array(7, 'InvalidArgumentException');
    $tests[] = array('No\Namespace\Allowed\ClassName', 'InvalidArgumentException');
    
    return $tests;
  }
}
?>