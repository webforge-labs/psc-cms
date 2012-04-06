<?php

namespace Psc\Code;

class AnnotationTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Annotation';
    parent::setUp();
  }

  public function testGetAnnotationName() {
    $anno = new SimpleMyAnnotation();
    
    $this->assertEquals('Psc\Code\SimpleMyAnnotation', $anno->getAnnotationName());
  }
  
  public function createAnnotation() {
    return new Annotation();
  }
}

class SimpleMyAnnotation extends Annotation {
  
}
?>