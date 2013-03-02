<?php

namespace Psc\Code\Test\Mock;

use RuntimeException;

class Expectation {
  
  protected $expects;
  protected $method;
  protected $with;
  protected $will;
  
  public function __construct($expects = NULL, $method = NULL, $with = NULL, $will = NULL) {
    $this->expects = $expects;
    $this->method = $method;
    $this->with = $with;
    $this->will = $will;
  }
  
  public function create() {
    return new static();
  }
  
  public function expects($expects) {
    $this->expects = $expects;
    return $this;
  }

  public function with($with) {
    $this->with = $with;
    return $this;
  }

  public function will($will) {
    $this->will = $will;
    return $this;
  }

  public function method($method) {
    $this->method = $method;
    return $this;
  }
  
  public function applyToMock($mock) {
    if (!isset($this->expects)) {
      throw new RuntimeException('expects muss in Expectation gesetzt sein. '.$this);
    }
    
    if (!isset($this->method)) {
      throw new RuntimeException('method muss in Expectation gesetzt sein. '.$this);
    }
    
    $expectation = $mock
      ->expects($this->expects)
      ->method($this->method);
    
    if ($this->with) {
      $expectation->with($this->with);
    }
      
    if ($this->will) {
      $expectation->will($this->will);
    }
    
    return $this;
  }
  
  public function getMethod() {
    return $this->method;
  }
  
  public function __toString() {
    return sprintf('Expectation: method %s. expects: %s', $this->method, $this->expects ? get_class($this->expects) : 'none');
  }
}
