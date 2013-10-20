<?php

namespace Psc\Code\Test;

use Webforge\Types\Type;
use Webforge\Types\StringType;
use Webforge\Types\IntegerType;
use Webforge\Types\BooleanType;
use Webforge\Types\ArrayType;
use Webforge\Common\ArrayUtil AS A;
use Psc\Data\RandomGenerator;
use ReflectionClass;
use Psc\Code\Code;

class CodeTester extends \Psc\SimpleObject {
  
  public $object;
  
  protected $testCase;
  
  protected $chars = array();
  
  protected $randomGenerator;
  
  public function __construct(\Psc\Code\Test\Base $testCase, RandomGenerator $randomGenerator = NULL) {
    $this->testCase = $testCase;
    $this->randomGenerator = $randomGenerator ?: new RandomGenerator;
    $this->setUp();
  }
  
  protected function setUp() {
  }
  
  /**
   * @return Psc\Code\Test\GClassTester
   */
  public function gClass(\Psc\Code\Generate\GClass $gClass) {
    return new \Psc\Code\Test\GClassTester($gClass, $this->testCase);
  }
  
  /**
   * Überprüft ob der Getter des Objektes funktioniert
   *
   * - muss nach dem Setzen den richtigen Wert zurückgeben
   *
   */
  public function getter($propertyName, Type $type) {
    $this->assertPre();
    
    $get = $this->generateGetter($propertyName);
    
    $this->testCase->assertSame($this->testCase->readAttribute($this->object, $propertyName),
                                $get($this->object),
                                sprintf("Getter für '%s' gibt nicht den richtigen Wert zurück",$propertyName)
                               );
  }
  
  /**
   * Überprüft ob der Setter des Objektes funktioniert
   *
   * der Setter:
   * - muss den richtigen Wert im Objekt setzen
   * - muss chainable sein
   */
  public function setter($propertyName, Type $type = NULL, $expected = NULL, $flags = 0x000000, $attributeName = NULL) {
    $this->assertPre();
    $attributeName = $attributeName ?: $propertyName;
    
    $set = $this->generateSetter($propertyName);
    
    if (func_num_args() === 2 || $expected === '::random')
      $expected = $this->generateRandomData($type);
      
    $this->testCase->assertAttributeNotSame($expected, $attributeName, $this->object,
                                              sprintf("Objekt hat Wert: %s für property '%s' schon gesetzt! (schlechter Test)",
                                                      Code::varInfo($expected), $propertyName));
    
    $this->assertChainable($set($this->object, $expected),
                           sprintf("Setter für property '%s' ist nicht chainable'", $propertyName));
    $this->testCase->assertAttributeEquals($expected, $attributeName, $this->object,
                                  sprintf("Getter für '%s' gab nicht das zurück, was der Setter gesetzt hat",$propertyName));
  }
  
  /**
   * Tested ob alle Values in $values für $propertyName in $this->object gesetzt werden können
   *
   * damit gehts schöner! @TODO
   *    $this->assertAttributeEquals(
   *       'baz',  /* expected value 
   *       'bar',  /* attribute name 
   *       new Foo /* object         
   *     );
   * @param array|mixed $values wird dies übergeben, werden keine random daten übergeben, sondern diese Values getestet
   */
  public function setterValues($propertyName, $values) {
    $this->assertPre();
    $get = $this->generateGetter($propertyName);
    $set = $this->generateSetter($propertyName);
    
    if (!is_array($values)) $values = array($values);
    
    foreach ($values as $expected) {
      $this->setter($propertyName, NULL, $expected);
    }
  }
  
  /**
   * Tested ob alle Values in $exceptionValues beim Setzen von $propertyName eine $exceptionClass-Exception auslösen
   */
  public function setterException($propertyName, $exceptionClass, $exceptionValues) {
    $this->assertPre();
    
    if (!is_array($exceptionValues)) $exceptionValues = array($exceptionValues);
    
    $object = $this->object;
    $set = $this->generateSetter($propertyName);
    foreach ($exceptionValues as $exceptionValue) {
      $this->testCase->assertException($exceptionClass, function () use ($object, $exceptionValue, $set) {
        $set($object, $exceptionValue);
      },
      NULL,
      NULL,
      'ExceptionValue ist: '.Code::varInfo($exceptionValue)
      );
    }
  }
  
  /**
   * Tested die Übergabe von Parametern durch den Constructor
   * 
   * @param array $signatur schlüssel sind die namen der properties. Values sind entweder Werte für einen konkreten Test, oder ein \Webforge\Types\Type (dann wird mit random daten getestet)
   * @TODO YAGNI: hier könnte man auch noch als dritten Parameter $expected übergeben (falls man setter hat im constructor die, die Daten ummodellieren)
   */
  public function constructor($class, Array $signatur) {
    $params = array();
    foreach ($signatur as $property => $type) {
      if ($type instanceof Type) {
        $params[$property] = $this->generateRandomData($type);
      } else {
        $params[$property] = $type;
      }
    }
    $expected = $params;
    
    // Objekt erstellen mit random daten
    $reflection = new ReflectionClass($class);
    $constructedObject = $reflection->newInstanceArgs($params);
    
    // gucken ob jeder Getter die Daten des übergebenen hat
    foreach ($signatur as $property => $type) {
      $get = $this->generateGetter($property);
      $this->testCase->assertSame($expected[$property], $get($constructedObject), sprintf("Property '%s' wurde nicht korrekt im Constructor gesetzt.",$property));
    }
  }
  
  protected function assertPre() {
    $this->testCase->assertNotEmpty($this->object,'Für den CodeTester muss $test->object gesetzt sein');
  }
  
  protected function assertChainable($actual) {
    $this->testCase->assertInstanceOf(get_class($this->object),$actual);
  }
  
  public function generateRandomData(Type $type) {
    return $this->randomGenerator->generateData($type);
  }
  
  public function generateRandomDataRandomType(Array $types) {
    return $this->randomGenerator->generateDataRandomType($types);
  }
  
  protected function generateGetter($property) {
    return Code::castGetter($property);
  }

  protected function generateSetter($property) {
    return Code::castSetter($property);
  }
}
