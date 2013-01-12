<?php

namespace Psc\Data\Type;

class CompositeTypeTestCase extends \Psc\Code\Test\Base {
  
  protected $typeName;
  
  public function assertPreConditions() {
    $this->assertNotEmpty($this->typeName,'Bitte den $typeName auf den Namen des CompositeTypes setzen');
  }
  
  public function testConstruct() {
    $composite = Type::create($this->typeName);
    
    return $composite;
  }
  
  public function testAtLeastTwoComponentsDefined() {
    $composite = $this->testConstruct(); // das ist dann einfacher im testCaseTest zu testen
    
    // ein composite besteht aus mindestens 2 Componenten, diese müssen im Constructor mit setComposites() deklariert werden
    try {
      $this->assertInstanceOf('Psc\Data\Type\Type', $composite->getComponent(1));
      $this->assertInstanceOf('Psc\Data\Type\Type', $composite->getComponent(2));
    } catch (\OutOfBoundsException $e) {
      $this->fail($e->getMessage()); // nicht throwen, wir sind ein testcase und eine exception ist ein error kein failure
    }
    return $composite;
  }
  
  /**
   * @depends testAtLeastTwoComponentsDefined
   * @expectedException OutOfBoundsException
   */
  public function testGetComponentMaxBound($composite) {
    $count = count($composite->getComponents());
    $composite->getComponent($count+1);
  }

  /**
   * @depends testAtLeastTwoComponentsDefined
   * @expectedException OutOfBoundsException
   */
  public function testGetComponent0Bound($composite) {
    $count = count($composite->getComponents());
    $composite->getComponent(0);
  }
  
}
?>