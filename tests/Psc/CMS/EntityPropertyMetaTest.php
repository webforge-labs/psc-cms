<?php

namespace Psc\CMS;

use Psc\Code\Generate\GClass;
use Webforge\Types\PersistentCollectionType;
use Webforge\Types\EntityType;

/**
 * @group class:Psc\CMS\EntityPropertyMeta
 */
class EntityPropertyMetaTest extends \Psc\Code\Test\Base {
  
  protected $entityPropertyMeta;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntityPropertyMeta';
    parent::setUp();
    $this->entityPropertyMeta = new EntityPropertyMeta(
      'translations',
      new PersistentCollectionType(new GClass('Entities\User')),
      'Übersetzungen'
    );

    $this->singleEntityPropertyMeta = new EntityPropertyMeta(
      'person',
      new EntityType(new GClass('Entities\Person')),
      'zuständige Person'
    );
  }
  
  public function testAcceptance() {
    $this->assertTrue($this->entityPropertyMeta->isRelation());
    $this->assertEquals('Entities\User',$this->entityPropertyMeta->getRelationEntityClass()->getFQN());
  }
  
  public function testEntityTypeIsRelation() {
    $this->assertTrue($this->singleEntityPropertyMeta->isRelation());
  }
  
  public function testGetEntityClass() {
    $this->assertEquals('Entities\User', $this->entityPropertyMeta->getRelationEntityClass()->getFQN());
    $this->assertEquals('Entities\Person', $this->singleEntityPropertyMeta->getRelationEntityClass()->getFQN());
  }
}
