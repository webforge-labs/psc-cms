<?php

namespace Psc\PHPUnit;

class InvokedAtMethodGroupIndexMatcherTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\PHPUnit\\InvokedAtMethodGroupIndexMatcher';
    parent::setUp();
  }
  
  public function testAtGroupMockingInvocationMatching() {
    $mock = $this->getMock('Psc\PHPUnit\TestObject2');
    
    $this->methodGroup = array('doSomething', 'doSomethingElse');
    
    $mock->expects($this->atMethodGroup('doSomething', 0, $this->methodGroup))->method('doSomething')->with('p0');
    $mock->expects($this->atMethodGroup('doSomethingElse', 1, $this->methodGroup))->method('doSomethingElse')->with('p1');
    $mock->expects($this->atMethodGroup('doSomething', 2, $this->methodGroup))->method('doSomething')->with('p2');
    $mock->expects($this->atMethodGroup('doSomethingElse', 3, $this->methodGroup))->method('doSomethingElse')->with('p2');
    
    $mock->doIgnored();
    $mock->doSomething('p0');
    $mock->doIgnored();
    $mock->doSomethingElse('p1');
    $mock->doSomething('p2');
    $mock->doIgnored();
    $mock->doIgnored();
    $mock->doIgnored();
    $mock->doSomethingElse('p2');
  }
}

class TestObject2 {
  
  public function doSomething($what) {
    
  }

  public function doSomethingElse($what) {
    
  }
  
  
  public function doIgnored() {
    
  }
}
?>