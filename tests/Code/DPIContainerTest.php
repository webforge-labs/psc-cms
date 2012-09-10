<?php

namespace Psc\Code;

/**
 * @group class:Psc\Code\DPIContainer
 */
class DPIContainerTest extends \Psc\Code\Test\Base {
  
  protected $dPIContainer;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\DPIContainer';
    parent::setUp();
    $this->it = 300;
  }
  
  public function testBenchmarkDPIWithIsset() {
    for ($i=1; $i<=$this->it; $i++) {
      $this->container = new IssetDPIContainer();
      $object = $this->container->getObject();
      $this->assertInstanceOf(__NAMESPACE__.'\MyInjectedObject', $object);
    }
  }

  public function testBenchmarkDPIWithClosure() {
    for ($i=1; $i<=$this->it; $i++) {
      $this->container = new ClosureDPIContainer();
      $object = $this->container->getObject();
      $this->assertInstanceOf(__NAMESPACE__.'\MyInjectedObject', $object);
    }
  }
}

class MyInjectedObject {
  
}
?>