<?php

namespace Psc\Code\Test\Mock;

class AtMethodGroupExpectation extends Expectation {
  
  protected $groupName;
  
  public function __construct($groupName, $method = NULL, $with = NULL, $will = NULL) {
    parent::__construct(NULL, $method, $with, $will);
    $this->groupName = $groupName;
  }
  
  public function getGroupName() {
    return $this->groupName;
  }
}
