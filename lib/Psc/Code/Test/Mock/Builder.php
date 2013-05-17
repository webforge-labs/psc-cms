<?php

namespace Psc\Code\Test\Mock;

use Psc\Code\Callback;
use PHPUnit_Framework_MockObject_Stub_MatcherCollection;
use PHPUnit_Framework_MockObject_Matcher_Invocation;
use ReflectionClass;

/**
 * Eine Klasse die eine kleine DSL für das erstellen eines Mocks erstellt
 *
 *
 * okay: das ist ein bißchen PHPUnit intern, ist aber nicht so schwer - wenn man erstmal durch die ewig langen namen durch ist:
 *
 * normalerweise machen wir ja:
 *
 * $mock = $testCase->getMock(...)
 * $mock->expects()
 *      ->method()
 *      ->will()
 *      ->with()
 *
 * expects() gibt einen PHPUnit_Framework_MockObject_Builder_InvocationMocker zurück (welcher eine PHPUnit_Framework_MockObject_InvocationMocker buildet)
 * dieser Builder wrapped einen matcher welcher ein PHPUnit_Framework_MockObject_Matcher_Invocation ist
 * Das Objekt Matcher_Invocation hat also die inhalte von method(), will(), with() usw
 *
 * der MockObject_InvocationMocker implementiert eine PHPUnit_Framework_MockObject_Stub_MatcherCollection also quasi mehrere aufrufe von expects()
 * diese collection wird dann in das richtige Mockobject $mock gegeben. (dies kann man in mocked_class.tpl.dist in MockObject\Generator\ gut sehen)
 * Da wir zu unserem Build-Zeitpunkt aber noch kein fertiges Mock-Object haben (da wir noch methods hinzufügen wollen), tun wir in der API so als würde das
 * expectsxxxx() der Unterklassen ganz normal auf einem MockObject()->expects() aufrufen,indem wir den Builder_InvocationMocker zurückgeben und die Match_Invocations in $this->matchers sammeln.
 * Wir tun also so als wären wir selbst ein PHPUnit_Framework_MockObject_InvocationMocker und müssen dann nur unsere matchers zu dem InvocationMocker vom MockObject kopieren
 */
abstract class Builder extends \Psc\Code\Test\AssertionsBase implements PHPUnit_Framework_MockObject_Stub_MatcherCollection { // damit können wir z.b. $this->once() und sowas
  
  protected $atGroups = array();
  protected $atGroupsIndexes = array();

  protected $testCase;
  
  // ich glaub die brauchen wir nicht, weil wir ja eh über den doublesmanager losgehen
  //public static function create() {
  //  $args = func_num_args();
  //  $refl = new ReflectionClass(get_called_class());
  //  
  //  return $refl->newInstanceArgs($args);
  //}
  
  protected $fqn;
  
  protected $methods = array();

  /**
   * @var PHPUnit_Framework_MockObject_Matcher_Invocation[]
   */
  protected $matchers = array();
  
  /**
   * @param Psc\Code\Test\Mock\Expectation[]
   */
  protected $expectations = array();
  
  /**
   * @var bool
   */
  protected $mockAllMethods = FALSE;
  
  public function __construct(\Psc\Code\Test\Base $testCase, $fqn, Array $methods= array()) {
    parent::__construct($name = NULL, $data = array(), $dataName = '');
    $this->testCase = $testCase;
    $this->methods = $methods;
    $this->fqn = $fqn;
    $this->expectations = array();
  }
  
  protected function addMethod($methodName) {
    $this->methods[$methodName] = $methodName;
    return $this;
  }

  /**
   * Adds a new matcher to the collection which can be used as an expectation
   * or a stub.
   *
   * @param PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
   *        Matcher for invocations to mock objects.
   */
  public function addMatcher(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher) {
    $this->matchers[] = $matcher;
    return $this;
  }
  
  abstract public function build();
  
  /**
   * Helper für KindKlassen
   *
   */
  protected function buildMock(array $constructorParams = array(), $callConstructor = TRUE) {
    $mock = $this->testCase->getMock(
        $this->fqn,
        $this->mockAllMethods ? array() : $this->methods,
        $constructorParams,
        '', // mockclassname
        $callConstructor
    );
    
    $this->applyMatchers($mock);
    
    return $mock;
  }
  
  /**
   * @param der zweite parameter kann this->once oder sowas sein
   * @return InvocationMockerBuilder
   */
  protected function buildExpectation($method, PHPUnit_Framework_MockObject_Matcher_Invocation $matcher = NULL) {
    $this->addMethod($method);
    
    // dieser kann dann will und method und with
    $mockerBuilder = new InvocationMockerBuilder( // fügt sich selbst zu uns hinzu
      $this, $matcher ?: $this->any()
    );
    return $mockerBuilder->method($method);
  }

  /**
   * @return Expectation
   */
  protected function buildAtMethodGroupExpectation($method, $groupName, $with = NULL, $will = NULL) {
    $this->addExpectation(
      $expectation = new AtMethodGroupExpectation($groupName, $method, $with, $will)
    );
    
    return $expectation;
  }
  
  /**
   * @chainable
   */
  protected function addExpectation(Expectation $expectation) {
    $this->addMethod($expectation->getMethod());
    
    if ($expectation instanceof AtMethodGroupExpectation) {
      if (!isset($this->atGroups[$expectation->getGroupName()])) {
        $this->atGroups[$expectation->getGroupName()] = array();
      }

      if (!isset($this->atGroupsIndexes[$expectation->getGroupName()])) {
        $this->atGroupsIndexes[$expectation->getGroupName()] = 0;
      }
      
      $this->atGroups[$expectation->getGroupName()][] = $expectation->getMethod();
    }
    
    $this->expectations[] = $expectation;
    return $this;
  }

  /**
   * Applied alle Matchers (Aufrufe von expects()) an unser MockObject
   * 
   */
  protected function applyMatchers($mock) {
    // alle gesammelten aufrufe von expects "kopieren" wir hier ins mock object
    foreach ($this->matchers as $matcher) {
      /*
       * $matcher ist ein PHPUnit_Framework_MockObject_Matcher, dieser hat public properties,
         die wir in den matcher den das mock objekt erstellt kopieren müssen
         leider können wir im mock dieses objekt nur durch expects erstellen.
         wenn es einen setter gäbe, könnten wird das "schöner" lösen (vll kann man die mock templates überschreiben?)
      */
      $mockMatcher = $mock->expects($matcher->invocationMatcher)->getMatcher();
      
      // copy
      foreach ($matcher as $publicProperty => $value) {
        $mockMatcher->$publicProperty = $value;
      }
    }
    
    $this->applyExpectations($mock);
    
    return $this;
  }
  
  protected function applyExpectations($mock) {
    foreach ($this->expectations as $expectation) {
      
      if ($expectation instanceof AtMethodGroupExpectation) {
        $expectation->expects(
          $this->atMethodGroup(
            $expectation->getMethod(),
            $this->atGroupsIndexes[$expectation->getGroupName()]++,
            $this->atGroups[$expectation->getGroupName()]
          )
        );
      }
      
      $expectation->applyToMock($mock);
    }
  }

  
  /**
   * @param Psc\Code\Test\Base $testCase
   * @chainable
   */
  public function setTestCase(\Psc\Code\Test\Base $testCase) {
    $this->testCase = $testCase;
    return $this;
  }

  /**
   * @return Psc\Code\Test\Base
   */
  public function getTestCase() {
    return $this->testCase;
  }
}
?>