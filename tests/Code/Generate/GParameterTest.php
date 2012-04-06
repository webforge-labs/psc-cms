<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\GParameter;
use ReflectionClass;
use Psc\A;


/**
 * @group generate
 */
class GParameterTest extends \Psc\Code\Test\Base {

  public function testParameter() {
    
    $class = new ReflectionClass('Psc\Code\Generate\TestClass');
    $method = $class->getMethod('comboBox');
    
    $params = $method->getParameters();
    
    $param = new GParameter();
    $param->elevate($params[4]);
    
    $this->assertEquals('commonItemData',$param->getName());
    $this->assertEquals(array(),$param->getDefault());
    $this->assertTrue($param->isOptional());
    $this->assertEquals('Array $commonItemData = array()',$param->php());
    
    $param = new GParameter();
    $param->elevate($params[3]);
    $this->assertEquals('$itemType = NULL',$param->php());

    $param = new GParameter();
    $param->elevate($params[0]);
    $this->assertEquals('$label',$param->php());
    
    $param = new GParameter();
    $param->elevate(A::index($class->getMethod('factory')->getParameters(),0));
    $this->assertEquals('\Psc\Code\Generate\TestHint $dunno',$param->php($useFQN = TRUE));
  }
  
  public function testBugParameterArrayHint() {
    $param = new GParameter('justAnArray', 'Array');
    $this->assertTrue($param->isArray());
    $this->assertNull($param->getHint());
  }
  
  public function testHintClassNotAutoloadable() {
    $param = new GParameter('justAParam');
    $param->setHint('Cls\Does\Not\Exists');
    $this->assertInstanceOf('Psc\Code\Generate\GClass',$param->getHint());
  }
}

abstract class TestClass {

  protected $prop1 = 'banane';

  public static $prop2 = NULL;

  public function comboBox($label, $name, $selected = NULL, $itemType = NULL, Array $commonItemData = array()) {
    // does not matter

    $oderDoch = true;
  }

  public static function factory(TestHint $dunno) {
  }

  abstract public function banane();

  public function method2($num, Array $p1, stdClass $std = NULL, $bun = array()) {
    $bimbam = 'pling';
    
    return 'schnurpsel';
  }
}

if (!class_exists(__NAMESPACE__.'\TestHint', FALSE)) {
class TestHint {
}
}
?>