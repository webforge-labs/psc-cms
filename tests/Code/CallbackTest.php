<?php

namespace Psc\Code;

use Psc\Code\Callback;

/**
 * @group class:Psc\Code\Callback
 */
class CallbackTest extends \Psc\Code\Test\Base {
  
  public function testCallWithStatics() {
    $object = $this->getMock('Psc\Code\CBClass', array('getsCalled'));
    $object->expects($this->once())->method('getsCalled')
            ->with($this->equalTo('static1'),
                   $this->equalTo('static2'),
                   $this->equalTo(array('dynamic1')),
                   $this->equalTo(7)
                  )
            ->will($this->returnValue('dont trust the mock'));
    
    $callback = new Callback($object, 'getsCalled', array('static1','static2'));
    $this->assertEquals('dont trust the mock', $callback->call(Array(array('dynamic1'), 7)));
  }
  
  // @TODO arguments für alle (und statische)
  public function testStaticCall() {
    $callback = new Callback('Psc\Code\CBClass', 'sayStatic');
    $this->assertEquals($callback->getType(),Callback::TYPE_STATIC_METHOD);
    $this->assertEquals('static:hello', $callback->call());
  }

  public function testMethodCall() {
    $callback = new Callback(new \Psc\Code\CBClass(), 'sayMethod');
    $this->assertEquals($callback->getType(),Callback::TYPE_METHOD);
    $this->assertEquals('method:hello', $callback->call());
  }
  
  public function testFunctionCall() {
    $callback = new Callback(NULL, 'Psc\Code\sayFunction');
    $this->assertEquals($callback->getType(),Callback::TYPE_FUNCTION);
    $this->assertEquals('function:hello', $callback->call());
  }

  public function testClosureCall() {
    $closure = function () {
      return 'closure:hello';
    };
    
    $callback = new Callback($closure);
    $this->assertEquals($callback->getType(),Callback::TYPE_CLOSURE);
    $this->assertEquals('closure:hello', $callback->call());

    // both allowed
    $callback = new Callback(NULL,$closure);
    $this->assertEquals($callback->getType(),Callback::TYPE_CLOSURE);
    $this->assertEquals('closure:hello', $callback->call());
  }
  
  public function testCalledChangesState() {
    $callback = new Callback(function () {
      // empty
    });
    
    $this->assertFalse($callback->wasCalled());
    $this->assertEquals(0, $callback->getCalled());
    
    $callback->call();
    
    $this->assertTrue($callback->wasCalled());
    $this->assertEquals(1, $callback->getCalled());
    
    $callback->call();
    $this->assertEquals(2, $callback->getCalled());
  }
}


class CBClass {
  
  public static function sayStatic() {
    return 'static:hello';
  }
  
  public function sayMethod() {
    return 'method:hello';
  }
  
  public function getsCalled() {
    return func_get_args();
  }
}

function sayFunction() {
  return 'function:hello';
}

// closure siehe oben
?>