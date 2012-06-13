<?php

namespace Psc\Code\Test;

use Psc\Code\Test\CodeTester;
use Psc\Data\Type\StringType;
use Psc\Data\Type\IntegerType;
use Psc\Data\Type\ArrayType;
use Psc\Data\Type\Type;
use Psc\Code\Code;
use Closure;

/**
 * @group class:Psc\Code\Test\CodeTester
 */
class CodeTesterTest extends \Psc\Code\Test\Base {
  
  protected $it = 40;

  public function setUp() {
    $this->chainClass = 'Psc\Code\Test\CodeTester';
    parent::setUp();
  }
  
  // leider nur acceptance-tests aber immerhin
  
  public function testGetterTesting() {
    $this->assertTestOK($this->createGetterTestCase('TestingClass', new StringType()));
    $this->assertTestFailure($this->createGetterTestCase('NotGettingClass', new StringType()));
  }

  public function testSetterTesting() {
    $this->assertTestOK($this->createSetterTestCase('TestingClass',new StringType()));
    $this->assertTestFailure($this->createSetterTestCase('NotChainedSetterClass',new StringType()));
    $this->assertTestFailure($this->createSetterTestCase('NotSettingClass', new StringType()));
  }
  
  public function testSetterTesting_ExpectedGiven() {
    $this->assertTestOK($this->createSetterTestCaseWithExpected('TestingClass',NULL,'prop1','myValue'));
  }
  
  public function testSetterValuesTesting_OKMulti() {
    $object = $this->createObject('TestingClass');
    $closure = function ($that) use ($object) {
      $codeTester = $that->getCodeTester();
      $codeTester->object = $object;
      $codeTester->setterValues('valuesProperty', array(TestingClass::MYVALUE1,TestingClass::MYVALUE2));
    };
    $this->assertTestOK($this->createTestCase($closure,'values_multi_ok'));
  }
  
  public function testSetterValuesTesting_OKSingle() {
    $object = $this->createObject('TestingClass');
    $closure = function ($that) use ($object) {
      $codeTester = $that->getCodeTester();
      $codeTester->object = $object;
      $codeTester->setterValues('valuesProperty', TestingClass::MYVALUE1);
    };
    $this->assertTestOK($this->createTestCase($closure,'values_single_ok'));
  }

  public function testSetterValuesTesting_MultiFailure() {
    $object = $this->createObject('TestingClass');
    $closure = function ($that) use ($object) {
      $codeTester = $that->getCodeTester();
      $codeTester->object = $object;
      $codeTester->setterException('valuesProperty', 'Psc\Code\WrongValueException', array('ich bin falsch',TestingClass::MYVALUE3));
    };
    $this->assertTestOK($this->createTestCase($closure,'values_multi_failure'));
  }

  // multi meint: alle müssen eine value sein die failed
  public function testSetterValuesTesting_MultiFailure_wrongIdea() {
    $object = $this->createObject('TestingClass');
    $closure = function ($that) use ($object) {
      $codeTester = $that->getCodeTester();
      $codeTester->object = $object;
      $codeTester->setterException('valuesProperty', 'Psc\Code\WrongValueException', array(TestingClass::MYVALUE2,TestingClass::MYVALUE3));
    };
    $this->assertTestFailure($this->createTestCase($closure,'values_multi_failure_idea'));
    // TestCase failed, weil myvalue2 erlaubt ist
  }

  public function testSetterValuesTesting_SingleFailure() {
    $object = $this->createObject('TestingClass');
    $closure = function ($that) use ($object) {
      $codeTester = $that->getCodeTester();
      $codeTester->object = $object;
      $codeTester->setterException('valuesProperty', 'Psc\Code\WrongValueException', TestingClass::MYVALUE3);
    };
    $this->assertTestOK($this->createTestCase($closure,'values_single_fail'));
  }
  
  public function testConstructorTesting() {
    $sig = array('prop1'=>new StringType(), 'prop2'=>new StringType());
    $this->assertTestOK($this->createConstructorTestCase('TestingClass', $sig));
    
    $this->assertTestFailure($this->createConstructorTestCase('BadConstructorP1Class', $sig));
    $this->assertTestFailure($this->createConstructorTestCase('BadConstructorP2Class', $sig));
  }
  
  public function testConstructorTesting_needsCorrectOrderOfConstructorArguments() {
    // vertauschen geht net
    $sig = array('prop2'=>new StringType(), 'prop1'=>new StringType());
    $this->assertTestFailure($this->createConstructorTestCase('TestingClass', $sig));
  }

  /**
   * Erstellt einen TestCase der den für den CodeTester getter() aufruft
   */
  protected function createGetterTestCase($class, $type, $property = 'prop1') {
    $object = $this->createObject($class);
    
    return 
      new ClosureTestCase(function ($that) use ($type, $object, $property) {
        $codeTester = $that->getCodeTester();
        $codeTester->object = $object;
        $codeTester->getter($property, $type);
      },'GetterTest');
  }
  
  protected function createTestCase(Closure $closure, $label = 'unkown Testcase') {
    return new ClosureTestCase($closure,$label);
  }

  /**
   * Erstellt einen TestCase der den für den CodeTester setter() aufruft
   */
  protected function createSetterTestCase($class, $type, $property = 'prop1') {
    $object = $this->createObject($class);
    
    return 
      new ClosureTestCase(function ($that) use ($type, $object, $property) {
        $codeTester = $that->getCodeTester();
        $codeTester->object = $object;
        $codeTester->setter($property,$type);
      },'SetterTest');
  }

    /**
   * Erstellt einen TestCase der den für den CodeTester setter() aufruft
   */
  protected function createSetterTestCaseWithExpected($class, $type, $property, $expected) {
    $object = $this->createObject($class);
    
    return 
      new ClosureTestCase(function ($that) use ($type, $object, $property, $expected) {
        $codeTester = $that->getCodeTester();
        $codeTester->object = $object;
        $codeTester->setter($property,$type,$expected);
      },'SetterTest');
  }

  /**
   * Erstellt einen TestCase der den für den CodeTester constructor aufruft
   */
  protected function createConstructorTestCase($class, $constructorSignature = array()) {
    $class = 'Psc\Code\Test\\'.$class;
    return 
      new ClosureTestCase(function ($that) use ($constructorSignature, $class) {
        $codeTester = $that->getCodeTester();
        $codeTester->constructor($class,$constructorSignature);
      },'ConstructorTest');
  }
  
  /**
   * Erstellt das Objekt, welches der CodeTester im TestCase untersuchen soll
   */
  protected function createObject($class) {
    $c = 'Psc\Code\Test\\'.$class;
    return new $c('prop1concreteValue','prop2concreteValue');
  }


  public function createCodeTester($testCase = NULL) {
    return new CodeTester($testCase ?: $this);
  }
}

// in dieser Klasse ist alles korrekt
class TestingClass {
  
  const MYVALUE1 = 'myvalue1';
  const MYVALUE2 = 'myvalue2';
  const MYVALUE3 = 'myvalue3_not_allowed';
  
  protected $prop1;
  protected $prop2;
  
  protected $valuesProperty;
  
  public function __construct($p1Value, $p2Value) {
    $this->prop1 = $p1Value;
    $this->prop2 = $p2Value;
  }
  
  public function getProp1() {
    return $this->prop1;
  }
  
  public function getValuesProperty() {
    return $this->valuesProperty;
  }
  
  public function setValuesProperty($value) {
    Code::value($value, self::MYVALUE1, self::MYVALUE2);
    $this->valuesProperty = $value;
    return $this;
  }
  
  public function setProp1($p1Value) {
    $this->prop1 = $p1Value;
    return $this;
  }

  public function getProp2() {
    return $this->prop2;
  }
  
  public function setProp2($p2Value) {
    $this->prop2 = $p2Value;
    return $this;
  }
}

class NotSettingClass extends TestingClass {
  
  public function setProp1($p1Value) {
  
  }
}

class NotGettingClass extends TestingClass {
  
  public function getProp1() {
  
  }
}

class NotChainedSetterClass extends TestingClass {
  
  // setter ist nicht chainable
  public function setProp1($p1Value) {
    $this->prop1 = $p1Value;
  }
}


class BadConstructorP1Class extends TestingClass {
  
  public function __construct($p1Value, $p2Value) {
    $this->prop2 = $p2Value;
  }
  
}

class BadConstructorP2Class extends TestingClass {
  
  public function __construct($p1Value, $p2Value) {
    $this->prop1 = $p1Value;
  }
  
}
?>