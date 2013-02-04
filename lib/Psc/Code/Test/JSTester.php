<?php

namespace Psc\Code\Test;

use Psc\JS\jQuery;
use Psc\Code\Code;
use Psc\HTML\JooseBase;
use Psc\JS\JooseSnippetWidget;
use Psc\JS\JooseSnippet;

class JSTester extends \Psc\SimpleObject {
  
  protected $testCase;
  
  protected $joose;
  
  protected $jooseSnippet;
  
  public function __construct(Base $testCase, JooseBase $html = NULL) {
    $this->testCase = $testCase;
    $this->joose = $html;
    if ($this->joose instanceof JooseSnippetWidget) {
      $this->jooseSnippet = $this->joose->getJooseSnippet();
    }
  }

  /**
   * @chainable
   */
  public function constructsJoose($jooseClass, Array $params = array()) {
    if (isset($this->jooseSnippet)) {
      $this->testCase->assertEquals($jooseClass, $this->jooseSnippet->getClass());
    } else {
      $this->testCase->assertEquals($jooseClass, $this->joose->getJooseClass());
    }
    
    foreach ($params as $param => $constraint) {
      $this->hasParam($param, $constraint);
    }
    
    return $this;
  }
  
  /**
   * @chainable
   */
  public function hasParam($name, $constraint = NULL) {
    $params = $this->getConstructParams();
    
    $this->testCase->assertArrayHasKey($name, $params);
    
    if (isset($constraint)) {
      $this->testCase->assertThat($params[$name], $constraint);
    }
    
    return $this;
  }
  
  public function getParam($name) {
    $params = $this->getConstructParams();
    $this->testCase->assertArrayHasKey($name, $params);
    return $params[$name];
  }
  
  public function getConstructParams() {
    if (isset($this->jooseSnippet)) {
      return (array) $this->jooseSnippet->getConstructParams();
    } else {
      return $this->joose->getConstructParams();
    }
  }
  
  public function setJooseSnippet(JooseSnippet $snip) {
    $this->jooseSnippet = $snip;
    return $this;
  }
}
  
