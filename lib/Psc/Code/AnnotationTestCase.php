<?php

namespace Psc\Code;

use Psc\Code\Generate\GClass;

class AnnotationTestCase extends \Psc\Code\Test\Base {
  
  protected $annotation;
  
  protected $parser;
  
  public function setUp() {
    $this->assertNotEmpty($this->annotation, 'Bitte $this->annotation vor parent::setUp() setzen');
    parent::setUp();
    $this->test->object = $this->annotation;

    //$this->parser = new DocParser;
    //$this->parser->addNamespace('Doctrine\ORM\Mapping');
  }

  public function testHasName() {
    $this->assertNotEmpty($this->annotation->getAnnotationName());
  }
  
  public function testIsWithAnnotationAnnotationAnnotated() {
    $gClass = GClass::factory($this->annotation->getAnnotationName());
    $msg = 'Die Klasse: '.$this->annotation->getAnnotationName().' muss einen Class-Docblock mit @Annotation haben. Sonst erkennt der Doctrine AnnotationReader die Klasse nicht als Annotation an!';
    $this->assertTrue($gClass->hasDocBlock(), $msg);
    $this->assertTrue($gClass->getDocBlock()->hasSimpleAnnotation('Annotation'), $msg);
  }

  /**
   * @return Doctrine\Common\Annotations\AnnotationReader
   */
  public function createAnnotationReader() {
    // nicht SimpleAnnotationReader, denn der schaut nicht nach use - aliasen
    $reader = new \Doctrine\Common\Annotations\AnnotationReader();
    $this->assertTrue(class_exists('Psc\Code\Compile\Annotations\Property',FALSE));
    return $reader;
  }

  public function loadFixtureGClass($className) {
    $file = $this->getFile('fixture.'.$className.'.php');
    require_once $file;
    
    $gClass = GClass::factory('Psc\Code\Compile\Tests\\'.$className);
    return $gClass;
  }
  
  public function testInstanceOfAnnotation() {
    $this->assertInstanceOf('Psc\Code\Annotation', $this->annotation,'Jede Annotation muss Psc\Code\Annotation ableiten/implementieren');
  }
}
?>