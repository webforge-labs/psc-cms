<?php

namespace Psc\PHPUnit;

class InvokedAtMethodIndexMatcherTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\PHPUnit\\InvokedAtMethodIndexMatcher';
    parent::setUp();
  }
  
  public function testAtMockingInvocationMatching() {
    $mock = $this->getMock('Psc\PHPUnit\TestObject');
    
    $mock->expects($this->atMethod('doSomething', 0))->method('doSomething')->with('p0');
    $mock->expects($this->atMethod('doSomething', 1))->method('doSomething')->with('p1');
    $mock->expects($this->atMethod('doSomething', 2))->method('doSomething')->with('p2');
    
    $mock->doIgnored();
    $mock->doSomething('p0');
    $mock->doIgnored();
    $mock->doSomething('p1');
    $mock->doSomething('p2');
  }
}

class TestObject {
  
  public function doSomething($what) {
    
  }
  
  
  public function doIgnored() {
    
  }
}
?>