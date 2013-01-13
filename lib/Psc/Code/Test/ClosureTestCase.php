<?php

namespace Psc\Code\Test;

class ClosureTestCase extends \Psc\Code\Test\Base {

  protected $closure;
  
  public function __construct(\Closure $closure, $label = 'anonymous Closure') {
    $this->closure = $closure;
    parent::__construct('testClosure', array($closure), $label);
  }
  
  public function testClosure($closure) {
    $closure($this);
  }
}
?>