<?php

namespace Psc\Code\Compile;

use Psc\Code\Generate\GClass;
use Psc\Code\Generate\GParameter;

/**
 * @group class:Psc\Code\Compile\AddPropertyExtension
 */
class AddPropertyExtensionTest extends \Psc\Code\Test\Base {
  
  protected $addPropertyExtension;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Compile\AddPropertyExtension';
    parent::setUp();
    $this->type = $this->createType('PositiveInteger');
    $this->extension = new AddPropertyExtension('oid', $this->type, 'OID');
  }
  
  public function testAddPropertyToEmptyClass() {
    $gClass = new GClass('NoProperties');
    
    $this->extension->compile($gClass);
    
    $this->test->gClass($gClass)
      ->hasProperty('oid')
      ->hasMethod('getOID')
      ->hasMethod('setOID', array('oid'));
      
    $property = $gClass->getProperty('oid');
    $this->assertContains('@var int', $property->getDocBlock()->toString());
  }
  
  public function testAppendToEmptyConstructor() {
    $gClass = new GClass('NoProperties');
    
    $this->extension->setGenerateInConstructor(AddPropertyExtension::CONSTRUCTOR_APPEND);
    $this->extension->compile($gClass);
    
    $constructor = $this->test->gClass($gClass)
      ->hasMethod('__construct', array('oid'))
      ->get();
      
    $this->assertContains('$this->setOID($oid);',$constructor->php());
  }

  public function testAppendToNotEmptyConstructor() {
    $gClass = new GClass('NoProperties');
    $gClass->createMethod('__construct', array(new GParameter('someP1')));
    
    $this->extension
      ->setGenerateInConstructor(AddPropertyExtension::CONSTRUCTOR_APPEND)
      ->compile($gClass);
    
    $constructor = $this->test->gClass($gClass)
      ->hasMethod('__construct', array('someP1', 'oid'))
      ->get();
  }

  public function testPrependToNotEmptyConstructor() {
    // regression, weil das irgendwie nicht ging, hää
    
    $gClass = new GClass('NoProperties');
    $gClass->createMethod('__construct', array(new GParameter('someP1')));
    
    $this->extension
      ->setGenerateInConstructor(0) //AddPropertyExtension::CONSTRUCTOR_PREPEND)
      ->compile($gClass);
    
    $constructor = $this->test->gClass($gClass)
      ->hasMethod('__construct', array('oid','someP1'))
      ->get();
  }
}
?>