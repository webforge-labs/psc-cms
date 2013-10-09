<?php

namespace Psc\Doctrine;

use Psc\Doctrine\EntityBuilder;
use Psc\Code\Generate\GClass;
use Psc\Code\Code;

/**
 * @group entity-building
 * @group class:Psc\Doctrine\EntityBuilder
 *
 * @TODO dieser test würde viel schöner werden, wenn wir einfach einen Acceptance-Test draus machen würden.
 * wir würden ein Entity erzeugen und schauen ob all die Behaviour die wir wollen in der ClassMetadata von Doctrine ankommt
 */
class EntityBuilderTest extends \Psc\Code\Test\Base {
  
  protected $module;
  
  protected $entityBuilder;
  
  public function setUp() {
    $this->module = $this->getModule('Doctrine');
  }
  
  public function assertPreConditions() {
    $this->assertEquals('Psc\Entities',$this->module->getEntitiesNamespace());
  }

  public function testEntityCreateAcceptance() {
    $entityFile = $this->newFile('PersonEntity.php');
    $entityFile->delete();
    $entityRepositoryFile = $this->newFile('PersonEntityRepository.php');
    $entityRepositoryFile->delete();
    
    // test
    $builder = new EntityBuilder('Person', $this->module);
    $builder->setWithRepository(TRUE);
    $builder->buildDefaultV2();
    $file = $builder->write($entityFile, EntityBuilder::OVERWRITE);
    $this->assertSame($file, $builder->getWrittenFile());
    $repoFile = $builder->writeRepository($entityRepositoryFile, EntityBuilder::OVERWRITE);
    
    $this->assertFileEquals($entityFile,$file);
    $this->assertFileEquals($entityRepositoryFile, $repoFile);
    
    $fixtureEntityFile = $this->getFile('fixture.PersonEntity.php');
    $fixtureEntityRepositoryFile = $this->getFile('fixture.PersonEntityRepository.php');
    
    $this->assertFileEquals($fixtureEntityFile, $entityFile);
    $this->assertFileEquals($fixtureEntityRepositoryFile, $entityRepositoryFile);
  }
  
  public function testCreateProperty() {
    $builder = new EntityBuilder('Person', $this->module);
    
    $this->assertInstanceOf('Psc\Code\Generate\ClassBuilderProperty',
                            $builder->createProperty('email', $this->createType('Email')));
    
    $this->assertTrue($builder->getGClass()->hasProperty('email'));
    $this->assertTrue($builder->getGClass()->hasMethod('getEmail'));
    $this->assertTrue($builder->getGClass()->hasMethod('setEmail'));
    $this->assertEquals($this->createType('Email'),$builder->getProperty('email')->getType());
  }
  
  public function testCreateI18nProperty() {
    $builder = new EntityBuilder('Person', $this->module);
    $builder->setLanguages(array('en','de'));
    
    $this->assertInstanceOf('Psc\Code\Generate\ClassBuilderProperty',
                            $property = $builder->createProperty('text', $this->createType('Text'), EntityBuilder::I18N)
                          );
    
    $this->assertTrue($builder->getGClass()->hasProperty('i18nText'));
    $this->assertTrue($builder->getGClass()->hasProperty('textDe'));
    $this->assertTrue($builder->getGClass()->hasProperty('textEn'));
    $this->assertTrue($builder->getGClass()->hasMethod('getI18nText'));
    $this->assertTrue($builder->getGClass()->hasMethod('setI18nText'));
    $this->assertTrue($builder->getGClass()->hasMethod('getText'));
    $this->assertTrue($builder->getGClass()->hasMethod('setText'));
    
    $this->assertFalse($builder->getGClass()->hasMethod('setTextDe'));
    $this->assertFalse($builder->getGClass()->hasMethod('getTextDe'));
    $this->assertFalse($builder->getGClass()->hasMethod('setTextEn'));
    $this->assertFalse($builder->getGClass()->hasMethod('getTextEn'));
  }
  
  public function testCreatePropertyNullFlag() {
    $builder = new EntityBuilder('Person', $this->module);
    
    $this->assertInstanceOf('Psc\Code\Generate\ClassBuilderProperty',
                            $builder->createProperty('email', $this->createType('Email'), EntityBuilder::NULLABLE));
    $this->assertTrue($builder->getGClass()->hasProperty('email'));
    $property = $builder->getGClass()->getProperty('email');
    $columnAnnotation = $this->assertHasDCAnnotation($property->getDocBlock(), 'Column');
    $this->assertTrue($columnAnnotation->nullable, 'nullable ist nicht TRUE obwohl nullflag übergeben wurde');
  }
  
  public function testBuildMetaSetter() {
    $builder = new EntityBuilder('Person', $this->module);
    
    $builder->createDefaultId();
    $builder->createProperty('email', $this->createType('Email'));
    $builder->createProperty('name', $this->createType('String'));
    $builder->createProperty('firstName', $this->createType('String'));
    $builder->createProperty('birthday', $this->createType('DateTime'));
    
    $builder->buildSetMetaGetter();
    
    $class = $builder->getGClass();
    
    $this->assertTrue($class->hasMethod('getSetMeta'));
    $metaGet = $class->getMethod('getSetMeta');
    
    //attention: fragile test here
    $metaGetPHP = <<< 'PHP'
public static function getSetMeta() {
  return new \Psc\Data\SetMeta(array(
    'id' => new \Psc\Data\Type\IdType(),
    'email' => new \Psc\Data\Type\EmailType(),
    'name' => new \Psc\Data\Type\StringType(),
    'firstName' => new \Psc\Data\Type\StringType(),
    'birthday' => new \Psc\Data\Type\DateTimeType(),
  ));
}
PHP;
    $this->assertEquals($metaGetPHP, $metaGet->php());
  }
}
