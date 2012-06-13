<?php

namespace Psc;

use Psc\DependencyManager;

/**
 * @group class:Psc\DependencyManager
 */
class DependencyManagerTest extends \Psc\Code\Test\Base {

  /**
   * @var Psc\DependencyManager
   */
  public $depManager;

  public function testConstruct() {
    $this->depManager = new DependencyManager('testing');
  }
  
  /**
   * @depends testConstruct
   * @expectedException \Psc\DependencyException
   */
  public function testFunctions() {
    $this->depManager = new DependencyManager('testing');
    
    $this->depManager->register('/bla/test','test');
    
    $this->depManager->register('/bla/test2');
    
    $this->depManager->enqueue('test');
    $this->depManager->enqueue('test2');
    
    $this->depManager->unregister('test2');
    
    $this->depManager->enqueue('test3');
  }
}
?>