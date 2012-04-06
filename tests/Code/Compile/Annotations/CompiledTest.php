<?php

namespace Psc\Code\Compile\Annotations;

class CompiledTest extends \Psc\Code\AnnotationTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Compile\Annotations\Compiled';
    $this->annotation = $this->createCompiled();
    parent::setUp();
  }
  
  public function createCompiled() {
    return new Compiled();
  }
}
?>