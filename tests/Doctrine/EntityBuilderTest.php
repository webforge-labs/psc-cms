<?php

namespace Psc\Doctrine;

use Psc\Doctrine\EntityBuilder;
use Psc\Code\Generate\GClass;
use Psc\Code\Code;

/**
 * @group entity-building
 *
 * @TODO dieser test würde viel schöner werden, wenn wir einfach einen Acceptance-Test draus machen würden.
 * wir würden ein Entity erzeugen und schauen ob all die Behaviour die wir wollen in der ClassMetadata von Doctrine ankommt
 */
class EntityBuilderTest extends \Psc\Code\Test\Base {
  
  protected $module;
  
  protected $entityBuilder;
  
  public function setUp() {
    $this->module = \Psc\PSC::getProject()->getModule('Doctrine');
  }
  
  public function assertPreConditions() {
    $this->assertEquals('Entities',$this->module->getEntitiesNamespace());
  }

  public function testEntityCreateAcceptance() {
    $entityFile = $this->newFile('PersonEntity.php');
    $entityFile->delete();
    $entityRepositoryFile = $this->newFile('PersonEntityRepository.php');
    $entityRepositoryFile->delete();
    
    // test
    $builder = new EntityBuilder('Person', $this->module);
    $builder->setWithRepository(TRUE);
    $builder->buildDefault();
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
  
  
  public function testEntityRelationBuilding_ManyToMany_Owning() {
    $m2m = $this->doTestEntityRelationBuilding_ManyToMany(EntityBuilder::SIDE_OWNING);
    $this->assertEquals('persons',$m2m->inversedBy);
    $this->assertNull($m2m->mappedBy);
  }
  
  public function testEntityRelationBuilding_ManyToMany_Inverse() {
    $m2m = $this->doTestEntityRelationBuilding_ManyToMany(EntityBuilder::SIDE_INVERSE);
    $this->assertEquals('persons',$m2m->mappedBy);
    $this->assertNull($m2m->inversedBy);
  }
    
  public function doTestEntityRelationBuilding_ManyToMany($side) {
    $entityBuilder = $builder = new EntityBuilder('Person', $this->module);
    
    $builder->addRelation(EntityBuilder::RELATION_MANY_TO_MANY, new GClass('Entities\Address'), $side, 'addresses');
    $m2m = $this->assertCommonRelation($builder, 'addresses', 'ManyToMany', 'Entities\Address');

    /* interface
       wir haben eine verbindung von person <-> address
       person soll die owning side sein. D. h. Person called in addAddress, Address::addPerson() (analog remove)
       
       ich möchte hier nicht im code nachgucken, ob addPerson gecalled wird, das ist mir zu invasiv, schöner wäre die
       Klasse selbst zu testen => Wir brauchen einen dynamischen Test
    */
    
    $gClass = $entityBuilder->getGClass();
    $this->assertTrue($gClass->hasMethod('addAddress'), 'addAddress - Methode fehlt');
    $this->assertTrue($gClass->hasMethod('removeAddress'), 'removeAddres - Methode fehlt');
    
    return $m2m;
  }
  
  /**
   * @expectedException Psc\Doctrine\EntityBuilderRelationException
   */
  public function testEntityRelationBuilding_OneToMany_isWrongSide() {
    $builder = new EntityBuilder('Person', $this->module);
    $builder->addRelation(EntityBuilder::RELATION_ONE_TO_MANY, new GClass('Entities\Address'), EntityBuilder::SIDE_OWNING, 'address');
  }

  public function testEntityRelationBuilding_OneToMany() {
    // address hat den fremdschlüssel für person. demnach nur das property 'person' und wird inversed von addresses
    // wir betrachten hier aber die person seite die das property 'addresses' hat
    $builder = new EntityBuilder('Person', $this->module);
    $builder->addRelation(EntityBuilder::RELATION_ONE_TO_MANY, new GClass('Entities\Address'), EntityBuilder::SIDE_INVERSE,'addresses');
    $o2m = $this->assertCommonRelation($builder, 'addresses', 'OneToMany', 'Entities\Address');
    
    $this->assertEquals('person',$o2m->mappedBy);
    
    $gClass = $builder->getGClass();
    $this->assertTrue($gClass->hasMethod('addAddress'));
    $this->assertTrue($gClass->hasMethod('removeAddress'));
  }

  /**
   * @expectedException Psc\Doctrine\EntityBuilderRelationException
   */
  public function testEntityRelationBuilding_ManyToOne_isWrongSide() {
    // beispiel ist falsch, aber egal
    $builder = new EntityBuilder('Person', $this->module);
    
    $builder->addRelation(EntityBuilder::RELATION_MANY_TO_ONE, new GClass('Entities\Address'), EntityBuilder::SIDE_INVERSE, 'addresses');
  }

  public function testEntityRelationBuilding_ManyToOne() {
    // address hat den fremdschlüssel für person. demnach nur das property 'person' und wird inversedv on addresses
    $builder = new EntityBuilder('Address', $this->module);
    
    $builder->addRelation(EntityBuilder::RELATION_MANY_TO_ONE, new GClass('Entities\Person'), EntityBuilder::SIDE_OWNING);
    $m2o = $this->assertCommonRelation($builder, 'person', 'ManyToOne', 'Entities\Person');
    $this->assertEquals('addresses',$m2o->inversedBy);
  }
  
  protected function assertCommonRelation(EntityBuilder $builder, $property, $annotationName, $targetEntity) {
    $this->assertTrue($builder->hasProperty($property),'Builder hat property: '.$property.' nicht');
    $property = $builder->getProperty($property);
    $this->assertNotNull($property->getDocBlock(),'DocBlock des Properties: '.$property->getName().' ist nicht gesetzt');
    $this->assertInstanceOf('Psc\Data\Type\Type',$type = $property->getType(),'Type des Properties: '.$property->getName().' ist nicht gesetzt');
    
    if ($type->getName() === 'PersistentCollection') {
      $innerType = $type->getType();
      $this->assertInstanceof('Psc\Data\Type\ObjectType',$innerType);
      $this->assertEquals($targetEntity, $innerType->getClass()->getFQN());
    } elseif ($type->getName() === 'Object') {
      $this->assertEquals($targetEntity, $type->getClass()->getFQN());
    } else {
      $this->fail('Type ('.$type->getName().') ist weder Collection noch Object für Property: '.$property->getName());
    }
    
    $relationAnnotation = $this->assertHasDCAnnotation($property->getDocBlock(), $annotationName);
    $this->assertEquals($targetEntity, $relationAnnotation->targetEntity);
    return $relationAnnotation;
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
?>