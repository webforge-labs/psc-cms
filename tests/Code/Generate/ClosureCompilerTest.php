<?php

namespace Psc\Code\Generate;

/**
 * @group class:Psc\Code\Generate\ClosureCompiler
 */
class ClosureCompilerTest extends \Psc\Code\Test\Base {
  
  protected $closureCompiler;
  protected $gClass;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Generate\ClosureCompiler';
    parent::setUp();
    
    $this->gClass = new GClass('MyClosureClass');
    $this->gClass->setNamespace(__NAMESPACE__);
    $this->closureCompiler = new ClosureCompiler();
    
  }
  
  public function testAssertPreConditions() {
    $that = new MyClosureClass();
    $this->assertEquals(array('m1p1'=>true,'m1p2'=>false), $that->method1(true,false));
    $this->assertEquals(array('m2p1'=>true,'m2p2'=>false), $that->method2(true,false));
  }
  
  public function testAcceptance() {
    list($code, $methodNames) = $this->closureCompiler->compile($this->gClass);
    
    $that = new MyClosureClass();
    eval($code);
    
    $this->assertInstanceOf('Closure', $method1);
    $this->assertEquals(array('m1p1'=>true,'m1p2'=>false), $method1(true,false));
    
    $this->assertInstanceOf('Closure', $method2);
    $this->assertEquals(array('m2p1'=>false,'m2p2'=>true), $method2(false,true));
    
    $this->assertEquals(array('method1','method2','var'), $methodNames);
  }
  
  public function testCCAlias() {
    list($code, $methodNames) = $this->closureCompiler->compile($this->gClass);
    
    $that = new MyClosureClass();
    eval($code);

    $this->assertTrue(isset($var), "\$var ist nicht definiert!\nPHP Code:\n".$code);
    $this->assertInstanceOf('Closure', $var, 'PHP Code war: '.$code);
    $this->assertTrue(!isset($varCannotBeUsedInPHP));
  }
}

class MyClosureClass {
  
  public function method1($m1p1, $m1p2) {
    return compact('m1p1', 'm1p2');
  }
  
  public function method2($m2p1, $m2p2) {
    return compact('m2p1', 'm2p2');
  }
  
  /**
   * @cc-alias var
   */
  public function varCannotBeUsedInPHP() {
    
  }
}
?>