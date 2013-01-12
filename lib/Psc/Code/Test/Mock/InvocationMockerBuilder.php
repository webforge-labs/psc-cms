<?php

namespace Psc\Code\Test\Mock;

use PHPUnit_Framework_MockObject_Stub_MatcherCollection;
use PHPUnit_Framework_MockObject_Matcher_Invocation;

/**
 * Ein Helferlein für unsere selbstgebauten Builder
 *
 * Ein InvocationMockerbBuilder kann so benutzt werden wie ein
 * 
 * $mock = $testCase->getMock()
 * $invocationMockerBuilder = $mock->expects($this->any);
 *
 * (kann also will() und with() und method() und so)
 * wäre
 */
class InvocationMockerBuilder extends \PHPUnit_Framework_MockObject_Builder_InvocationMocker {
  
  private $builder;
  
  // wir könnten auch diesen constructor nicht überschreiben
   // und sichergehen dass wir im Mock\Builder den Builder korrekt setzen
  
  public function __construct(PHPUnit_Framework_MockObject_Stub_MatcherCollection $collection, PHPUnit_Framework_MockObject_Matcher_Invocation $invocationMatcher) {
    $this->builder = $collection;
    parent::__construct($collection, $invocationMatcher);
  }
  
  public function end() {
    return $this->builder;
  }
  
  public function getBuilder() {
    return $this->builder;
  }
  
  // wir könnten hier auch eine __call magic methode nehmen, wo wir gucken ob sie mit expect anfängt und wenn ja dann an den builder-chain zurückgeben
  
  public function build() {
    return $this->builder->build();
  }
  
  //public function setBuilder(Builder $builder) {
  //  $this->builder = $builder;
  //}
}
?>