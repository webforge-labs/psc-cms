<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\GClass;
use ReflectionClass;

/**
 * @group class:Psc\Code\Generate\GClass
 * @group generate
 * @group entity-building
 */
class GClassTest extends \Psc\Code\Test\Base {
  
  public function testMethodOrdering() {
    $gClass = new GClass(__NAMESPACE__.'\\OrderedMethods');
    $gClass->setParentClass(new GClass(__NAMESPACE__.'\\MyDocTestClass'));
    
    $gClass->createMethod('getIdentifier');
    $gClass->createMethod('getObjectName');
    
    $gClass->createMethod('setLabel',array(new GParameter('label')));
    $gClass->createMethod('getLabel');
    
    $gClass->addMethod(new GMethod('__construct'), GClass::PREPEND);
    
    $classWriter = new ClassWriter($gClass);
    
    $classWriter->write($out = $this->newFile('class.OrderedMethods.php'), array(), ClassWriter::OVERWRITE);
    
    require $out;
    $gClass = GClass::factory(__NAMESPACE__.'\\OrderedMethods');
    
    $getMethodNames = function (Array $methods) {
      $methodNames = array();
      foreach ($methods as $method) {
        $methodNames[] = $method->getName();
      }
      return $methodNames;
    };
    
    $this->assertEquals(array('__construct','getIdentifier','getObjectName','setLabel','getLabel'), $getMethodNames($gClass->getMethods()));
    $this->assertEquals(array('__construct','getIdentifier','getObjectName','setLabel','getLabel','getName'), $getMethodNames($gClass->getAllMethods())); // parent methods behind
    
    $gClass->setMethodOrder($gClass->getMethod('getLabel'), 1);
    
    $this->assertEquals(array('__construct','getLabel','getIdentifier','getObjectName','setLabel'), $getMethodNames($gClass->getMethods()));
  }
  
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
  
  public function testFQNAndNotFQNClassesNamespaces() {
    $noFQN = new GClass('LParameter');
    $this->assertEquals(NULL, $noFQN->getNamespace());
    $this->assertEquals('\LParameter', $noFQN->getName()); // invariant: getName IMMER mit \\ davor, egal was passiert
    $this->assertEquals('LParameter', $noFQN->getFQN());
    
    $fqn = new GClass('\LParameter');
    $this->assertEquals('\\', $fqn->getNamespace());
    //$this->assertEquals('\LParameter', $fqn->getName());
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
  
  public function testIsAbstract() {
    // class is not elevated, so that is wrong!
    $gClass = new GClass(__NAMESPACE__.'\\MyAbstractClass');
    $this->assertFalse($gClass->isAbstract());

    // its right this way
    $gClass = GClass::factory(__NAMESPACE__.'\\MyAbstractClass');
    $this->assertTrue($gClass->isAbstract());
  }
  
  public function testWithGClassConstruction() {
    $gClass = new GClass('MyAbstractClass');
    $gClass->setNameSpace(__NAMESPACE__);
    
    $otherGClass = new GClass($gClass);
    $this->assertTrue($gClass->equals($otherGClass)); // don't rely on that $otherGClas === $gClass and don't rely on that $otherGClass !== $gClass
  }
  
  public function testNewInstance() {
    $gClass = new GClass('Psc\Exception');
    $exception = $gClass->newInstance(array('just a test error'));
    
    $this->assertInstanceOf('Psc\Exception', $exception);
    $this->assertEquals('just a test error', $exception->getMessage());
  }
  
  public function testNewInstanceWithoutConstructor() {
    $gClass = new GClass('MyConstructorThrowsExceptionClass');
    $gClass->setNamespace(__NAMESPACE__);
    $instance = $gClass->newInstance(array(), GClass::WITHOUT_CONSTRUCTOR);
    
    $this->assertInstanceOf($gClass->getFQN(), $instance);
    $this->assertTrue($instance->checkProperty);
  }

  public function testNewClassInstance() {
    $exception = GClass::newClassInstance('Psc\Exception', array('just a test error'));
    $this->assertInstanceOf('Psc\Exception', $exception);
    $this->assertEquals('just a test error', $exception->getMessage());

    $exception = GClass::newClassInstance($gClass = new GClass('Psc\Exception'), array('just a test error'));
    $this->assertInstanceOf('Psc\Exception', $exception);
    $this->assertEquals('just a test error', $exception->getMessage());

    $exception = GClass::newClassInstance($gClass->getReflection(), array('just a test error'));
    $this->assertInstanceOf('Psc\Exception', $exception);
    $this->assertEquals('just a test error', $exception->getMessage());
  }
  
  public function testInterfacesPHPGenerationAcceptance() {
    $gClass = GClass::factory(__NAMESPACE__.'\\MyTestClass');
    $gClass->addInterface(new GClass('\AnInterface'));
    
    $this->assertContains('class MyTestClass implements \AnInterface', $gClass->php());
  }
  
  public function testGetAllInterfaces() {
    $gClass = GClass::factory('Psc\CMS\Item\SelectComboBoxable');
    
    $interfaces = array();
    foreach ($gClass->getAllInterfaces() as $interface) {
      $interfaces[] = $interface->getClassName();
    }
    
    $this->assertArrayEquals(array('AutoCompletable', 'Identifyable'), $interfaces);
  }
  
  public function testOwnMethodForAbstractClassImplementingAnInterface() {
    $gClass = GClass::factory(__NAMESPACE__.'\\MyAbstractInterfacedClass');
    
    $this->assertFalse($gClass->hasOwnMethod('interfaceMethod1'));
    $this->assertTrue($gClass->hasMethod('interfaceMethod1'));
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

abstract class MyAbstractClass {
  
  abstract public function thisIsIt();
}

class MyConstructorThrowsExceptionClass {
  
  public $checkProperty = TRUE;
  
  public function __construct() {
    throw new \Psc\Exception('this should not be called');
  }
}

abstract class MyAbstractInterfacedClass implements MyInterface {
  
  abstract public function thisIsIt();
}

interface MyInterface {
  
  public function interfaceMethod1();
  
}
?>