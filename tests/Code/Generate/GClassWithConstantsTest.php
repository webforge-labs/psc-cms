<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\GClass;
use ReflectionClass;

/**
 * @group class:Psc\Code\Generate\GClass
 * @group generate
 * @group entity-building
 */
class GClassWithConstantsTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->gClass = new GClass('MyTestClassWithConstants');
    $this->gClass->setNamespace(__NAMESPACE__);
    $this->gClass->elevateClass();
  }
  
  public function testConstantsElevation() {
    $this->assertTrue($this->gClass->hasProperty('prop1'));
    
    $this->assertCount(2, $this->gClass->getConstants(), '2 Konstanten erwartet');
    $this->assertTrue($this->gClass->hasConstant('MULTIPLE'));
    $this->assertInstanceOf('Psc\Code\Generate\GClassConstant', $multiple = $this->gClass->getConstant('multiple'));
    $this->assertEquals(0x000001, $multiple->getValue());
    
    $this->assertTrue($this->gClass->hasConstant('unique')); // case insensitiv
    $this->assertInstanceOf('Psc\Code\Generate\GClassConstant', $unique = $this->gClass->getConstant('unique'));
    $this->assertEquals(0x000002, $unique->getValue());
    
    // da kommt leider integer raus
    //var_dump(gettype($unique->getValue()));
  }
  
  public function testAllConstantsElevation() {
    $this->assertCount(4, $this->gClass->getAllConstants(), '4 Konstanten erwartet');
  }
  
  public function testConstantPHPGeneration() {
    $multiple = $this->gClass->getConstant('multiple');
    
    $this->assertEquals('const MULTIPLE = 1', $multiple->php());
  }

  public function testConstantsInGClassPHPGenerationAcceptance() {
    $php = $this->gClass->php();
    
    $this->assertContains('const MULTIPLE', $php);
    $this->assertContains('const UNIQUE', $php);
    $this->assertNotContains('const PARENT1', $php);
    $this->assertNotContains('const PARENT2', $php);
  }
}

class MyTestClassWithConstants extends MyTestParentClassWithConstants {
  
  const MULTIPLE = 0x000001;
  const UNIQUE   = 0x000002;
  
  protected $prop1;
  
  public function getName() {
  }
  
  public function getId() {
  }
}

class MyTestParentClassWithConstants {
  
  const PARENT1 = 0x000010;
  const PARENT2 = 0x000020;
  
}
?>