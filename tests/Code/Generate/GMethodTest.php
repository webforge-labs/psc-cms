<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\GMethod;
use ReflectionClass;
use stdClass;
use Psc\String AS S;

/**
 * @group generate
 */
class GMethodTest extends \Psc\Code\Test\Base {
  
  public function testMethod() {
    $class = new ReflectionClass('Psc\Code\Generate\TestClass2');
    
    $factory = GMethod::reflectorFactory($class->getMethod('factory'));
    $method2 = GMethod::reflectorFactory($class->getMethod('method2'));
    $banane = GMethod::reflectorFactory($class->getMethod('banane'));
    
    $parameters = $method2->getParameters();
    foreach ($parameters as $param) {
      $this->assertInstanceOf('Psc\Code\Generate\GParameter', $param);
    }
    
    $cr = "\n";
    $factoryCode  = 'public static function factory(TestHint $dunno) {'.$cr;
    $factoryCode .= '}';
    
    $this->assertEquals(0, count($factory->getBodyCode()));
    $this->assertEquals('',$factory->getBody(0));
    
    $method2Code  = 'public function method2($num, Array $p1, stdClass $std = NULL, $bun = array()) { // does matter'.$cr;
    
    $body = NULL;
    $body .= '$bimbam = \'pling\';'.$cr;
    $body .= $cr;
    $body .= '// anotherinline comment'.$cr;
    $body .= 'return \'schnurpsel\';'.$cr;
    
    $method2Code .= S::indent($body,2,$cr);
    $method2Code .= '}';
    
    $method2->getBodyCode(); // 2 mal holen darf die anzahl nicht verdoppeln
    $this->assertEquals(4, count($method2->getBodyCode()));
    
    /* method2 method */
    $this->assertEquals($body, $method2->getBody(0), \Psc\String::debugEquals($body, $method2->getBody(0)));
    $this->assertEquals($method2Code, $method2->php(), \Psc\String::debugEquals($method2Code, $method2->php()));
    
    /* Factory method */
    $this->assertEquals(TRUE,$factory->isStatic());
    $this->assertEquals($factoryCode, $factory->php(), \Psc\String::debugEquals($factoryCode, $factory->php()));
    
    
    $this->assertEquals('abstract public function banane();', $banane->php(),
                        sprintf("output: '%s'", $banane->php())
                       );
  }
}

abstract class TestClass2 {
  
  protected $prop1 = 'banane';
  
  public static $prop2;
  
  public function comboBox($label, $name, $selected = NULL, $itemType = NULL, Array $commonItemData = array()) { 
    $oderDoch = true;
  }
  
  public static function factory(TestHint $dunno) {
  }
  
  abstract public function banane();
  
  public function method2($num, Array $p1, stdClass $std = NULL, $bun = array()) { // does matter
    $bimbam = 'pling';
    
    // anotherinline comment
    return 'schnurpsel';
  }
}

class TestHint {
}
?>