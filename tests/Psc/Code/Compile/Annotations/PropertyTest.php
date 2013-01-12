<?php

namespace Psc\Code\Compile\Annotations;

use Psc\Code\Generate\GClass;

/**
 * @group class:Psc\Code\Compile\Annotations\Property
 */
class PropertyTest extends \Psc\Code\AnnotationTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Compile\Annotations\Property';
    $this->annotation = $this->createProperty();
    parent::setUp();
  }
  
  public function testConstructorInjection() {
    $this->assertEquals('property1',$this->annotation->getName());
    $this->assertEquals(FALSE, $this->annotation->getGetter());
    $this->assertEquals(FALSE, $this->annotation->getSetter());
  }
  
  public function testStaticCreation() {
    $this->assertEquals($this->annotation, Property::create('property1',FALSE,FALSE));
  }
  
  public function testCreateDefaults() {
    $this->assertChainable($p = Property::create('property2'));
    $this->assertTrue($p->getGetter());
    $this->assertTrue($p->getSetter());
  }

  public function testAcceptance() {
    $gClass = $this->loadFixtureGClass('AnnotatedCompileClass');
    
    $annotations = $this->createAnnotationReader()->getClassAnnotations($gClass->getReflection());
    
    $this->assertEquals(array(
      Property::create('property1',$getter = TRUE, $setter = TRUE),
      Property::create('property2',$getter = TRUE, $setter = TRUE)
    ), $annotations);
  }
  
  public function createProperty() {
    return new Property(array('name'=>'property1','getter'=>FALSE,'setter'=>FALSE));
  }
}
?>