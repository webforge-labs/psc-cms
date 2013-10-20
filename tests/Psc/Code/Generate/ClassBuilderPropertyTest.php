<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\ClassBuilderProperty;
use Webforge\Types\StringType;

/**
 * @group generate
 * @group class:Psc\Code\Generate\ClassBuilderProperty
 */
class ClassBuilderPropertyTest extends \Psc\Code\Test\Base {
  
  protected $classBuilder;
  protected $cProperty = 'Psc\Code\Generate\ClassBuilderProperty';
  protected $cBuilder = 'Psc\Code\Generate\ClassBuilder';
  
  public function setUp() {
    $this->classBuilder = new ClassBuilder(new GClass('Psc\TPL\AST\Teaser'));
    $this->chainClass = $this->cProperty;
  }

  public function testConstruct() {
    $property = $this->createProperty('text');
    
    return $property;
  }
  
  /**
   * @depends testConstruct
   */
  public function testSetType($property) {
    $this->assertChainable($property->setType(new StringType()));
    return $property;
  }

  /**
   * @depends testSetType
   */
  public function testGetType($property) {
    $this->assertInstanceOf('Webforge\Types\Type', $property->getType());
    
    $this->assertEquals('string',$property->getDocType());
  }
  
  /**
   * @depends testConstrut
   */
  public function addPropertyReturnsClassBuilderToChain() {
    $this->assertInstanceOf($this->cBuilder, $prop->addProperty('nextProperty'));
  }

  /**
   * @depends testConstruct
   */
  public function testGetClassBuilder($property) {
    $this->assertInstanceof($this->cBuilder, $property->getClassBuilder());
  }
  
  public function testUpCaseName_overwritesDefault() {
    $property = $this->createProperty('oid');
    $this->assertEquals('Oid',$property->getUpcaseName());
    $this->assertChainable($property->setUpcaseName('OID'));
    $this->assertEquals('OID',$property->getUpcaseName());
  }
  
  public function testPropertyUnwraps() {
    $property = $this->createProperty('test');
    $this->assertInstanceof('Psc\Code\Generate\GProperty',$property->getGProperty());
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testConstructPropertyNameStartsWithAlpha() {
    $this->createProperty('1property');
  }

  public function testConstructPropertyNameContainsIntegers() {
    $this->createProperty('p1operty1');
  }

  public function testConstructPropertyNameBeginsWithUnderscore() {
    $this->createProperty('_1property');
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testConstructPropertyNameIsString() {
    $this->createProperty(array());
  }

  public function testCreateDocBlock_doesNotRecreate() {
    $prop = $this->createProperty('myniceprop');
    $dbExpected = $prop->createDocBlock();
    $dbActual = $prop->createDocBlock();
    $this->assertSame($dbExpected, $dbActual);
  }

  
  protected function createProperty($name) {
    $property = new ClassBuilderProperty($name, $this->classBuilder);
    
    return $property;
  }
}
