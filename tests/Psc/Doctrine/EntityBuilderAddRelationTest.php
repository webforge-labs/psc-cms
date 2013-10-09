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
class EntityBuilderAddRelationTest extends \Psc\Code\Test\Base {
  
  protected $module;
  
  protected $entityBuilder;
  
  public function setUp() {
    $this->module = $this->getModule('Doctrine');

    $this->game = new EntityRelationMeta(new GClass('Psc\Entities\Game'), 'source');
    $this->sound = new EntityRelationMeta(new GClass('Psc\Entities\Sound'), 'target');
  }
  
  public function assertPreConditions() {
    $this->assertEquals('Psc\Entities',$this->module->getEntitiesNamespace());
  }
  
  public function testManyToMany_Owning() {
    $m2m = $this->doTestManyToMany(EntityBuilder::SIDE_OWNING);
    $this->assertEquals('persons',$m2m->inversedBy,'inversedBy ist nicht korrekt');
    $this->assertNull($m2m->mappedBy);
  }
  
  public function testManyToMany_Inverse() {
    $m2m = $this->doTestManyToMany(EntityBuilder::SIDE_INVERSE);
    $this->assertEquals('persons',$m2m->mappedBy, 'mappedBy ist nicht korrekt');
    // wenn wir persons<->adresses erstellen und wir von Person aus betrachten muss mappedBy/InversedBy das property auf der inverse Side sein
    // das property auf der inverse side, heißt aber, wie unser property in mehrzahl
    $this->assertNull($m2m->inversedBy);
  }
    
  public function doTestManyToMany($side) {
    $entityBuilder = $builder = new EntityBuilder('Person', $this->module);
    $entityBuilder->createDefaultId();
    
    $builder->createRelation(EntityBuilder::RELATION_MANY_TO_MANY,
                          $entityBuilder->getEntityRelationMeta(),
                          new EntityRelationMeta(new GClass('Psc\Entities\Address'), 'target'),
                          $side
                         );
    $m2m = $this->assertCommonRelation($builder, 'addresses', 'ManyToMany', 'Psc\Entities\Address');

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
  
  public function testOneToMany() {
    // address hat den fremdschlüssel für person. demnach nur das property 'person' und wird inversed von addresses
    // wir betrachten hier aber die person seite die das property 'addresses' hat
    $builder = new EntityBuilder('Person', $this->module);
    $builder->createDefaultId();

    $builder->createRelation(EntityBuilder::RELATION_ONE_TO_MANY,
                          $builder->getEntityRelationMeta(),
                          new EntityRelationMeta(new GClass('Psc\Entities\Address'),'target'),
                          EntityBuilder::SIDE_INVERSE
                         );


    $o2m = $this->assertCommonRelation($builder, 'addresses', 'OneToMany', 'Psc\Entities\Address');
    
    $this->assertEquals('person',$o2m->mappedBy, 'mapped by');
    
    $gClass = $builder->getGClass();
    $this->assertTrue($gClass->hasMethod('addAddress'));
    $this->assertTrue($gClass->hasMethod('removeAddress'));
  }

  public function testManyToOne() {
    // address hat den fremdschlüssel für person. demnach nur das property 'person' und wird inversedv on addresses
    $builder = new EntityBuilder('Address', $this->module);
    $builder->createDefaultId();
    $builder->createRelation(EntityBuilder::RELATION_MANY_TO_ONE,
                          $builder->getEntityRelationMeta(),
                          new EntityRelationMeta(new GClass('Psc\Entities\Person'),'target'),
                          EntityBuilder::SIDE_OWNING
                         );

    $m2o = $this->assertCommonRelation($builder, 'person', 'ManyToOne', 'Psc\Entities\Person');
    $this->assertEquals('addresses',$m2o->inversedBy);
  }

  public function testManyToOne_Unidirectional() {
    $this->game2sound = new EntityRelation($this->game, $this->sound, EntityRelation::MANY_TO_ONE, EntityRelation::UNIDIRECTIONAL);
    $this->game2sound->setNullable(TRUE);

    $builder = new EntityBuilder('Game', $this->module);
    $builder->createDefaultId();
    $builder->buildRelation($this->game2sound);

    $m2o = $this->assertCommonRelation($builder, 'sound', 'ManyToOne', 'Psc\Entities\Sound');
    
    $this->assertNull($m2o->inversedBy);
  }
  
  protected function assertCommonRelation(EntityBuilder $builder, $property, $annotationName, $targetEntity) {
    $this->assertTrue($builder->hasProperty($property),'Builder hat property: '.$property.' nicht.');
    $property = $builder->getProperty($property);
    $this->assertNotNull($property->getDocBlock(),'DocBlock des Properties: '.$property->getName().' ist nicht gesetzt');
    $this->assertInstanceOf('Psc\Data\Type\Type',$type = $property->getType(),'Type des Properties: '.$property->getName().' ist nicht gesetzt');
    
    if ($type->getName() === 'PersistentCollection') {
      $innerType = $type->getType();
      $this->assertInstanceof('Psc\Data\Type\ObjectType',$innerType);
      $this->assertEquals($targetEntity, $innerType->getClass()->getFQN());
    } elseif ($type->getName() === 'Entity') {
      $this->assertEquals($targetEntity, $type->getClass()->getFQN());
    } else {
      $this->fail('Type ('.$type->getName().') ist weder Collection noch Object für Property: '.$property->getName());
    }
    
    $relationAnnotation = $this->assertHasDCAnnotation($property->getDocBlock(), $annotationName);
    $this->assertEquals($targetEntity, $relationAnnotation->targetEntity, 'Target Entity stimmt nicht überein');
    return $relationAnnotation;
  }
  
  
  public function testRelationInterface() {
    $this->markTestIncomplete('@TODO');
  }

  public function testRelationInterface_SetterInManyToOneAddsToManySideCollection() {
    $this->markTestIncomplete('@TODO siehe auch addXXX test');
  }

  public function testRelationInterface_SetterInManyToOneRemovesInManySideCollection() {
    $this->markTestIncomplete('@TODO should it?'); 
  }
  
  public function testRelationInterface_updateOtherSideSetToFalseDoesNotUpdateOtherSideInSetter() {
    $this->markTestIncomplete('@TODO siehe tiptoi: Transition<->Mode hier darf transition nicht bei mode::addTransition() aufrufen weil mode kein property transitions hat (es ist im compiler mit bidirectional=>false bei transition angegeben)');
  }

  public function testRelationInterface_ManyToOneCallsNotAddXXXWhenSideIsNotSet() {
    $this->markTestIncomplete('@TODO');
  }
}
?>