<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\GClass;
use ReflectionClass;

/**
 * @group class:Psc\Code\Generate\GClass
 * @group generate
 * @group entity-building
 */
class GClassPropertiesTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    parent::setUp();
    $this->childClass = new GClass('ChildClass');
    $this->childClass
      ->setNamespace(__NAMESPACE__)
      ->elevateClass();

    $this->parentClass = new GClass('ParentClass1');
    $this->parentClass
      ->setNamespace(__NAMESPACE__)
      ->elevateClass();
  }
  
  public function testGetPropertiesReturnsOnlyOwnProperties() {
    $this->assertEquals(array('child1'), $this->propertiesNames($this->childClass->getProperties()));
  }
  
  public function testGetAllPropertiesReturnsAllPropertiesOfClassHierarchy() {
    $this->assertEquals(array('child1','parent1','parent2'), $this->propertiesNames($this->childClass->getAllProperties()));
  }
  
  public function testDeclaringClassesAreCorrectlyElevated() {
    $this->assertEquals($this->childClass->getFQN(),
                        $this->childClass->getProperty('child1')->getDeclaringClass()->getFQN()
                       );
    $this->childClass->hasOwnProperty('child1');
  
    $this->assertEquals($this->parentClass->getFQN(),
                        $this->childClass->getProperty('parent1')->getDeclaringClass()->getFQN()
                       );

    $this->assertEquals($this->parentClass->getFQN(),
                        $this->childClass->getProperty('parent2')->getDeclaringClass()->getFQN()
                       );
  }
  
  protected function propertiesNames(Array $properties) {
    $names = array();
    foreach ($properties as $property) {
      $names[] = $property->getName();
    }
    return $names;
  }
}


class ChildClass extends ParentClass1 {
  
  protected $child1;
  
}

class ParentClass1 {
  
  protected $parent1;
  protected $parent2;
}
?>