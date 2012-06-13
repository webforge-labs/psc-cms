<?php

namespace Psc\Doctrine;

use Psc\Doctrine\ModelCompiler;
use Psc\Code\Generate\ClassWriter;
use Psc\PSC;
use Psc\Code\Generate\GClass;

/**
 * @group class:Psc\Doctrine\ModelCompiler
 * @group entity-building
 */
class ModelCompilerTest extends \Psc\Code\Test\Base {
  
  protected $module;
  protected $mc;
  protected $classWriter;

  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\ModelCompiler';
    $this->module = PSC::getProject()->getModule('Doctrine');
    $this->classWriter = $this->getMock('Psc\Code\Generate\ClassWriter');
    
    $this->mc = new ConcreteModelCompiler($this->module, $this->classWriter);
    parent::setUp();
  }
  
  public function testDependencyInjection() {
    $classWriter = new ClassWriter();
    
    // cw
    $mc = new ConcreteModelCompiler(NULL, $classWriter);
    $eb = $mc->createEntityBuilder('neuesEntity');
    $this->assertSame($classWriter,$eb->getClassWriter());
    
    $mc = new ConcreteModelCompiler(NULL, NULL);
    $eb = $mc->createEntityBuilder('neuesEntity');
    $this->assertNotSame($classWriter,$eb->getClassWriter());
    
    // module
    $module = new Module(PSC::getProject());
    $mc = new ConcreteModelCompiler($module, NULL);
    $eb = $mc->createEntityBuilder('neuesEntity');
    $this->assertSame($module,$eb->getModule());
    
    $mc = new ConcreteModelCompiler(NULL, NULL);
    $eb = $mc->createEntityBuilder('neuesEntity');
    $this->assertNotSame($module,$eb->getModule());
  }

  public function testClosureCompilers_entity() {
    extract($this->mc->getClosureHelpers());
    
    $this->mc->compile(
      $entity('Person')
    );
    
    $eb = $this->mc->getEntityBuilder();
    $this->assertEquals('Entities\CompiledPerson',$eb->getEntityClass());
    $this->assertEquals('Entities\Person', $this->mc->getOriginalEntityClass()->getFQN());
    $this->assertEquals('Psc\CMS\AbstractEntity', $eb->getBaseEntity()->getFQN());
    $this->assertHasDCAnnotation($eb->getClassDocBlock(),'MappedSuperclass');
    $this->assertHasDCAnnotation($eb->getClassDocBlock(),'Table');
  }

  public function testClosureCompilers_extends() {
    extract($this->mc->getClosureHelpers());
    $this->assertEquals($extends('ExtendedEntity'), new GClass('Entities\ExtendedEntity'));
    $this->assertEquals($extends('Some\Other\ExtendedClass'), new GClass('Some\Other\ExtendedClass'));
  }

  public function testClosureCompilers_type() {
    extract($this->mc->getClosureHelpers());
    $this->assertEquals($type('String'), $this->createType('String'));
    $this->assertEquals($type('Integer'), $this->createType('Integer'));
  }

  public function testCCs_defaultSettingsSetsDefaults() {
    extract($this->mc->getClosureHelpers());
    
    $settings = $defaultSettings(array());
    
    $this->assertAttributeEquals(TRUE, 'updateOtherSide', $settings);
    $this->assertAttributeEquals(TRUE, 'bidirectional', $settings);
  }
  
  public function testCCs_defaultSettingsRespectsSettings() {
    extract($this->mc->getClosureHelpers());
    
    $settings = $defaultSettings(array('updateOtherSide'=>FALSE,
                                       'bidirectional'=>FALSE)
                                 );
    
    $this->assertAttributeEquals(FALSE, 'updateOtherSide', $settings);
    $this->assertAttributeEquals(FALSE, 'bidirectional', $settings);
  }
  
  public function testCCs_defaultSettingsFindsWrongKeys() {
    extract($this->mc->getClosureHelpers());
    
    $this->setExpectedException('InvalidArgumentException');
    $defaultSettings(array('updateOtherside')); // look closely
  }


  public function testClosureCompilers_entityClass() {
    extract($this->mc->getClosureHelpers());
    $this->assertEquals($entityClass('ExtendedEntity'), new GClass('Entities\ExtendedEntity'));
    $this->assertEquals($entityClass('Some\Other\ExtendedEntity'), new GClass('Some\Other\ExtendedEntity')); // konsistent mit oben oder gar nicht irgendwie
  }

  public function testClosureCompilers_entity_extends() {
    extract($this->mc->getClosureHelpers());
    
    $this->mc->compile(
      $entity('Person', $extends('BasicPerson'))
    );
    
    $eb = $this->mc->getEntityBuilder();
    $this->assertEquals('Entities\CompiledPerson', $eb->getEntityClass());
    $this->assertEquals('Entities\Person', $this->mc->getOriginalEntityClass()->getFQN());
    $this->assertEquals('Entities\BasicPerson', $eb->getGClass()->getParentClass()->getFQN());
  }
  
  public function testClosureCompilers_defaultId() {
    extract($this->mc->getClosureHelpers());
    
    $this->mc->compile(
      $entity('Person'),
      $defaultId()
    );
    
    $eb = $this->mc->getEntityBuilder();
    $class = $eb->getGClass();
    
    $this->assertTrue($class->hasProperty('id'));
    $this->assertEquals($this->createType('Id'), $eb->getProperty('id')->getType());
    $id = $class->getProperty('id');
    $this->assertHasDCAnnotation($id->getDocBlock(), 'Id');
    $this->assertHasDCAnnotation($id->getDocBlock(), 'Column');
    $this->assertHasDCAnnotation($id->getDocBlock(), 'GeneratedValue');
    
    $this->assertTrue($class->hasMethod('getIdentifier'));
    $this->assertTrue($class->hasMethod('getId'));
  }
  
  public function testClosureCompilers_argument() {
    extract($this->mc->getClosureHelpers());
    
    $this->mc->compile(
      $entity('Person'),
      $defaultId(),
      $property('name', $type('String'))
    );
    
    $name = $this->mc->getEntityBuilder()->getProperty('name');
    
    $this->assertEquals(array('property'=>$name),$argument('name'));
    $this->assertEquals(array('property'=>$name, 'default'=>FALSE),$argument('name',FALSE));
    $this->assertEquals(array('property'=>$name, 'default'=>NULL),$argument('name',NULL));
  }
  
  /**
   * @group constructor
   */
  public function testClosureCompilers_constructor() {
    extract($this->mc->getClosureHelpers());
    
    $this->mc->compile(
      $entity('Person'),
        $defaultId(),
        $property('name', $type('String')),
        $property('email', $type('Email')),
        $property('list', $type('Array')),
      $constructor(
        $argument('name'),
        $argument('email',NULL),
        $argument('list',array())
      )
    );
    
    $class = $this->mc->getEntityBuilder()->getGClass();
    $this->assertTrue($class->hasMethod('__construct'));
    $m = $class->getMethod('__construct');
    
    $params = $m->getParameters();
    $this->assertEquals('name',$params[0]->getName());
    $this->assertEquals('email',$params[1]->getName());
    $this->assertTrue($params[1]->isOptional());
    $this->assertEquals(NULL, $params[1]->getDefault());
    $this->assertEquals('list',$params[2]->getName());
    $this->assertTrue($params[2]->isArray());
    $this->assertTrue($params[2]->isOptional());
    $this->assertEquals(array(), $params[2]->getDefault());
  }
  
  public function testAcceptance() {
    $this->classWriter
      ->expects($this->exactly(2))
      ->method('write')
      ->will($this->returnValue($this->newFile('notImportant.php')));
    
    $this->mc->compileAll();
    
    //print $this->mc->personBuilder->getGClass()->php();
    //print $this->mc->addressBuilder->getGClass()->php();
  }
  
  public function testSetOverwriteMode_TRUE() {
    $this->assertChainable($this->mc->setOverwriteMode(TRUE));
    $this->classWriter
      ->expects($this->exactly(1))
      ->method('write')
      ->with($this->anything(), $this->anything(), $this->equalTo(EntityBuilder::OVERWRITE));
    
    $this->mc->compilePerson();
  }
  
  public function testSetOverwriteMode_FALSE() {
    $this->assertChainable($this->mc->setOverwriteMode(FALSE));
    
    $this->classWriter
      ->expects($this->exactly(1))
      ->method('write')
      ->with($this->anything(), $this->anything(), $this->equalTo(NULL));
    
    $this->mc->compilePerson();
  }
  
  public function testFlagSetting() {
    extract($this->mc->getClosureHelpers());
    
    $this->mc->compile(
      $entity('Person'),
      $flag('NO_SET_META_GETTER')
    );
    
    $this->assertTrue(($this->mc->getFlags() & ModelCompiler::NO_SET_META_GETTER) === ModelCompiler::NO_SET_META_GETTER);
  }
}

class ConcreteModelCompiler extends ModelCompiler {
  
  public $personBuilder;
  public $addressBuilder;
  
  public function compileAll() {
    $this->personBuilder = $this->compilePerson();
    $this->addressBuilder = $this->compileAddress();
  }
  
  public function compilePerson() {
    extract($this->getClosureHelpers());
    
    return $this->compile(
      $entity('Person'),
        $defaultId(),
        $property('name', $type('String')),
        $property('firstName', $type('String')),
        $property('email', $type('Email')),
        $property('birthday', $type('DateTime')),
      $constructor(
        $argument('name'),
        $argument('email'),
        $argument('firstName', NULL),
        $argument('birthday', NULL)
      ),
      $manyToOne('Address')
    );
  }
  
  public function compileAddress() {
    extract($this->getClosureHelpers());
    
    return $this->compile(
      $entity('Address'),
        $defaultId(),
        $property('name', $type('String')),
        $property('firstName', $type('String')),
        $property('email', $type('Email')),
        $property('birthday', $type('Datetime')),
      $constructor(
        $argument('name'),
        $argument('email'),
        $argument('firstName', NULL),
        $argument('birthday', NULL)
      ),
      $oneToMany('Person')
    );
  }
}
?>