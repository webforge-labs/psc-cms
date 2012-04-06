<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\GClass;
use ReflectionClass;

/**
 * @group generate
 * @group entity-building
 */
class GClassTest extends \Psc\Code\Test\Base {
  
  public function testNamespaceAppend() {
    $gClass = new GClass('XML\Object');
    $this->assertEquals('\XML',$gClass->getNamespace());
    $this->assertEquals('XML\Object',$gClass->getFQN());
    $gClass->setNamespace('Psc\XML');
    $this->assertEquals('Psc\XML\Object',$gClass->getFQN());
    $this->assertEquals('\Psc\XML', $gClass->getNamespace());
    
    $gClass = new GClass('XML\Object');
    $gClass->setNamespace('Psc\Hitch'.$gClass->getNamespace());
    $this->assertEquals('Psc\Hitch\XML\Object',$gClass->getFQN());
    $this->assertEquals('\Psc\Hitch\XML', $gClass->getNamespace());

    $gClass = new GClass('\XML\Object');
    $gClass->setNamespace('Psc\Hitch'.$gClass->getNamespace());
    $this->assertEquals('Psc\Hitch\XML\Object',$gClass->getFQN());
    $this->assertEquals('\Psc\Hitch\XML', $gClass->getNamespace());

    $gClass = new GClass('XML\Object');
    $gClass->setNamespace('\Psc\Hitch'.$gClass->getNamespace());
    $this->assertEquals('Psc\Hitch\XML\Object',$gClass->getFQN());
    $this->assertEquals('\Psc\Hitch\XML', $gClass->getNamespace());
  }
  
  public function testPropertyCreation() {
    $gClass = new GClass('TestClass');
    
    // private $name;
    $name = $gClass->createProperty('name',GProperty::MODIFIER_PRIVATE);
    $this->assertInstanceOf('Psc\Code\Generate\GProperty',$name);
    $this->assertTrue($name->isPrivate());
    $this->assertEquals('name',$name->getName()); // hihi

    // protected $label = NULL;
    $label = $gClass->createProperty('label',GProperty::MODIFIER_PROTECTED, NULL);
    $this->assertInstanceOf('Psc\Code\Generate\GProperty',$label);
    $this->assertTrue($label->isProtected());
    $this->assertEquals('label',$label->getName());
    $this->assertEquals(NULL,$label->getDefaultValue());
    $this->assertTrue($label->hasDefaultValue());
    $gClass->removeDefaultValue($label);
    $this->assertFalse($label->hasDefaultValue());
    // protected $label = 'datgleischewiename';
    $this->assertInstanceOf('Psc\Code\Generate\GProperty', $label->setDefaultValue('datgleischewiename'));
    $this->assertEquals('datgleischewiename', $label->getDefaultValue());
    $this->assertTrue($gClass->hasDefaultValue($label));

    // public $unsafe;
    $unsafe = $gClass->createProperty('unsafe',GProperty::MODIFIER_PUBLIC);
    $this->assertInstanceOf('Psc\Code\Generate\GProperty',$unsafe);
    $this->assertTrue($unsafe->isPublic());
    $this->assertFalse($unsafe->hasDefaultValue());
    $this->assertFalse($gClass->hasDefaultValue($unsafe));

$classCode = <<< 'CLASS_END'
class TestClass {
  
  private $name;
  
  protected $label = 'datgleischewiename';
  
  public $unsafe;
}
CLASS_END;
    $this->assertEquals($classCode, $gClass->php());
    
  }
  
  public function testDocBlock() {
    
    $gClass = new GClass('Psc\XML\TestObjects\Bla');
    $gClass->createDocBlock()->addSimpleAnnotation('xml:XmlObject');
    
$classCode = <<< 'CLASS_END'
/**
 * @xml:XmlObject
 */
class Bla {
}
CLASS_END;

    $this->assertEquals($classCode,$gClass->php());
  }
  
  
  /**
   * 
   * @group parsing
   */
  public function testDocBlockElevation() {
    // kopiert von unten
    $classCode = <<< 'CLASS_END'
/**
 * Headline
 * 
 * Summary is here
 */
class MyDocTestClass {
  
  /**
   * The name of the Class
   * 
   * @var string
   */
  protected $name;
  
  /**
   * Returns the name
   * 
   * @return name
   */
  public function getName() {
  }
}
CLASS_END;

    $gClass = GClass::factory('Psc\Code\Generate\MyDocTestClass');
    $this->assertTrue($gClass->hasDocBlock(), 'Klasse hat keinen DocBlock elevated');
    $this->assertTrue($gClass->getProperty('name')->hasDocBlock(), 'Property: name hat keinen DocBlock elevated');
    $this->assertTrue($gClass->getMethod('getName')->hasDocBlock(), 'Method: getName hat keinen DocBlock elevated');
    
    $this->assertEquals($classCode, $gClass->php());
  }
  
  public function testOwnMethodsGetter() {
    $gClass = new GClass($refl = new ReflectionClass('Psc\Code\Compile\Compiler'));
    $this->assertEquals('\Psc\Code\Compile', $gClass->getNamespace());
    
    $gm = $gClass->getMethod('getClassName'); // getClassName ist aus Psc\SimpleObject und somit eine Methode einer Vaterklasse
    $m = $refl->getMethod('getClassName');
    $this->assertEquals((string) $m->getDeclaringClass()->getName(), (string) $gm->getDeclaringClass()->getFQN());
    
    $this->assertArrayHasKey('getClassName',$gClass->getAllMethods());
    $this->assertArrayNotHasKey('getClassName',$gClass->getMethods());
    
    $this->assertLessThan(count($gClass->getAllMethods()), count($gClass->getMethods()));
    
    $m = $gClass->createMethod('Test');
    $this->assertNotEmpty($m->getDeclaringClass());
    
    /* BUG: Namespace verändern verändert die Rückggabe der Methoden */
    $gClass = GClass::factory('Psc\Code\Generate\MyTestClass');
    $this->assertEquals(2,count($gClass->getMethods()));
    $this->assertEquals(2,count($gClass->getAllMethods()));
    
    $gClass->setNamespace('Psc\Other\Than\Before');
    $this->assertEquals(2,count($gClass->getMethods()));
    $this->assertEquals(2,count($gClass->getAllMethods()));
  }
  
  public function testHasInterface() {
    
    $gClass = new GClass('Psc\Data\ArrayCollection');
    $gClass->elevateClass();
    
    //foreach ($gClass->getInterfaces() as $if) {
      //print $if->getFQN()."\n";
    //}
    $this->assertTrue($gClass->hasInterface(new GClass('Doctrine\Common\Collections\Collection')));
    
  }
}

class MyTestClass {
  
  public function getName() {
  }
  
  public function getId() {
  }
}


/**
 * Headline
 *
 * Summary is here
 */
class MyDocTestClass {
  
  /**
   * The name of the Class
   *
   * @var string
   */
  protected $name;
  
  /**
   * Returns the name
   *
   * @return name
   */
  public function getName() {
  }
}
?>