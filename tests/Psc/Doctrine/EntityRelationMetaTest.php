<?php

namespace Psc\Doctrine;

use Psc\Code\Generate\GClass;

/**
 * @group class:Psc\Doctrine\EntityRelationMeta
 */
class EntityRelationMetaTest extends \Psc\Code\Test\Base {
  
  protected $entityRelationMeta;
  
  protected $source; // user
  protected $target;  // product
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\EntityRelationMeta';
    parent::setUp();
    

    $this->source = new EntityRelationMeta(new GClass('tiptoi\Entities\User'), EntityRelationMeta::SOURCE, EntityRelationMeta::COLLECTION_VALUED);
    $this->source->setIdentifier('email');
    
    $this->target = new EntityRelationMeta(new GClass('tiptoi\Entities\Product'), EntityRelationMeta::TARGET, EntityRelationMeta::COLLECTION_VALUED);
  }
  
  /**
   * Wir wollen die MetaInformationen für dies Many-To-Many Relation kapseln:
   *
   * SOURCE: User (User ist owning Side)
   * 
   * @ORM\ManyToMany(targetEntity="Product", inversedBy="users");
   * @ORM\JoinTable(name="users_products",
   *     joinColumns={@ORM\JoinColumn(name="user_email", referencedColumnName="email", onDelete="cascade")},
   *     inverseJoinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="cascade")}
   *    )
   * @var PersistentEntityCollection
   *
   * TARGET: Product (Product ist inverse Side)
   * 
   * @ORM\ManyToMany(targetEntity="User",mappedBy="products");
   * @ORM\JoinTable(name="users_products",
   *     joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="cascade")},
   *     inverseJoinColumns={@ORM\JoinColumn(name="user_email", referencedColumnName="email", onDelete="cascade")})
   * @var PersistentEntityCollection
   *
   * dabei betrachten wir die relation vom user aus
   */
  public function testIdentifierCanBeSetButDefaultsToId() {
    $this->assertEquals('email', $this->source->getIdentifier());
    $this->assertEquals('id', $this->target->getIdentifier());
  }
  
  public function testPropertyNamesDefault() {
    $this->assertEquals('users', $this->source->getPropertyName());
    $this->assertEquals('products', $this->target->getPropertyName());
  }
  
  public function testNameAlias() {
    $ermC = new EntityRelationMeta(new GClass('tiptoi\Entities\Product'), EntityRelationMeta::TARGET, EntityRelationMeta::COLLECTION_VALUED);
    $this->assertEquals('Product', $ermC->getName());
    
    $ermC->setAlias('OtherProduct');
    $this->assertEquals('OtherProduct', $ermC->getName());
    $this->assertEquals('otherProducts', $ermC->getPropertyName());
    $this->assertEquals('otherProduct', $ermC->getParamName());
  }
  
  public function testTypeAndGClass() {
    //$this->assertEquals('User', $this->source->getName());
    $this->assertEquals('source', $this->source->getType());
    
    //$this->assertEquals('Product', $this->target->getName());
    $this->assertEquals('target', $this->target->getType());
  }
  
  public function testPropertyTypeIsCollectionForCollectionValued() {
    // da unser fall ziemlich langweilig ist, hier ein neuer
    $ermC = new EntityRelationMeta(new GClass('tiptoi\Entities\Product'), EntityRelationMeta::TARGET, EntityRelationMeta::COLLECTION_VALUED);
    $this->assertInstanceOf('Webforge\Types\PersistentCollectionType', $ermC->getPropertyType());
  }
  
  public function testPropertyTypeIsEntityTypeForSingleValued() {
    $ermS = new EntityRelationMeta(new GClass('tiptoi\Entities\Product'), EntityRelationMeta::TARGET, EntityRelationMeta::SINGLE_VALUED);
    $this->assertInstanceOf('Webforge\Types\EntityType', $ermS->getPropertyType());
  }
  
  public function testPropertyNameCanBeSet() {
    $this->source->setPropertyName('favoriteUsers');
    $this->assertEquals('favoriteUsers', $this->source->getPropertyName());
  }
  
  public function testGetParamName() {
    $erm = new EntityRelationmeta(new GClass('tiptoi\Entities\OID'), EntityRelationMeta::TARGET, EntityRelationMeta::COLLECTION_VALUED);
    $this->assertEquals('oids',$erm->getPropertyName());
    $this->assertEquals('oid', $erm->getParamName());
  }
  
  public function testGetParamNameCanBetSet() {
    $erm = new EntityRelationmeta(new GClass('tiptoi\Entities\OID'), EntityRelationMeta::TARGET, EntityRelationMeta::COLLECTION_VALUED);
    $erm->setParamName('objectId');
    $this->assertEquals('oids',$erm->getPropertyName());
    $this->assertEquals('objectId', $erm->getParamName());
  }

  public function testMethodNameForSingleValued() {
    $ermS = new EntityRelationMeta(new GClass('tiptoi\Entities\Product'), EntityRelationMeta::TARGET, EntityRelationMeta::SINGLE_VALUED);
    
    $this->assertEquals('Product', $ermS->getMethodName(NULL));
    $this->assertEquals('Products', $ermS->getMethodName('plural'));
  }

  public function testMethodNameForCollectionValued() {
    $ermC = new EntityRelationMeta(new GClass('tiptoi\Entities\Product'), EntityRelationMeta::TARGET, EntityRelationMeta::COLLECTION_VALUED);
    
    $this->assertEquals('Products', $ermC->getMethodName(NULL));
    $this->assertEquals('Product', $ermC->getMethodName('singular'));
  }
}
?>