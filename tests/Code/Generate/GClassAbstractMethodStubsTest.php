<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\GClass;
use ReflectionClass;

/**
 * @group class:Psc\Code\Generate\GClass
 * @group generate
 * @group entity-building
 * @group stubs
 */
class GClassAbstractMethodStubsTest extends \Psc\Code\Test\Base {
  
  public function testGetAllMethods() {
    $gClass = new GClass(__NAMESPACE__.'\\ToStubTestClass');
    $gClass->elevateClass();
    
    $toImplement = array();
    foreach ($gClass->getAllMethods() as $method) {
      if ($method->isAbstract()) {
        $toImplement[] = $method->getName();
      }
    }
    
    $this->assertArrayEquals(array('implementIt','implementItFromParent','getName','generateDependency'), $toImplement);
  }

  public function testMethodGenerationAcceptance() {
    $gClass = new GClass('ToBeStubbedClass');
    $gClass->setParentClass(new GClass(__NAMESPACE__.'\\StubTestAbstractClass'));
    $gClass->addInterface(new GClass(__NAMESPACE__.'\\AnInterface'));
    $gClass->createAbstractMethodStubs();
    
    $this->test->gClass($gClass)
      ->hasMethod('getName')
      ->hasMethod('implementIt')
      ->hasMethod('implementItFromParent')
      ->hasMethod('generateDependency', array('deps', 'gClass'));
  }
}
  
abstract class StubTestAbstractClass {
  
  abstract public function getName();
  
  abstract public function generateDependency(Array $deps, \Psc\Code\Generate\GClass $gClass);  
}

interface AnInterfaceParent {
  
  public function implementItFromParent();
}

interface AnInterface extends AnInterfaceParent {
  
  public function implementIt($return = FALSE);
}

abstract class ToStubTestClass extends StubTestAbstractClass implements AnInterface {
  
}
?>