<?php

namespace Psc\Doctrine;

use Psc\Code\Generate\GClass;
use Psc\Code\Generate\DocBlock;
use Doctrine\ORM\Mapping AS ORM;

require_once __DIR__.DIRECTORY_SEPARATOR.'EntityRelationBaseTest.php';

/**
 * @group class:Psc\Doctrine\Relation
 *
 * finally: bau ichs doch ;)
 *
 * Anforderungen:
 *   - source und target (als EntityRelationMeta)
 *     - collectionValuedName
 *     - singleValuedName
 *     - gClass
 *     - name des identifiers
 *     - lowercase namen
 *   - type der Relation (MANY_TO_MANY, ONE_TO_MANY, MANY_TO_ONE, ONE_TO_ONE)
 *   - dir der Relation unidrectional oder bidirectional (gesehen von source aus)
 *   - owning side der Relation (verstellbar für many_to_many, kann NULL sein bei unidirectional (oder self referencing many to many))
 *   - nullable
 *   - cascade
 *   - order by der collectionSide
 *   - relationInterface: updateOtherSide (bool)
 *
 * muss die property / ies in einer fremden Klasse setzen können (bei ModelCompiler ist die gClass aus den EntityRelationmetas nicht dieselbe in die geschrieben wird)
 * erzeugt die Doctrine Annotations (diese testen wir)
 */
class EntityRelationTest extends EntityRelationBaseTest {
  
  protected $relation;
  
  /**
   *
   * Beispiele:
   *
   * ManyToMany:
   *  article<->tag  Owning Side: Article (denn da werden die Tags ja zugeordnet)
   *
   * ManyToOne bidirectional:
   *  oid<->product  Owning Side(geht ja auf einer Seite): OID,  product.oids ist die collectionSide
   *
   * OneToMany bidrectional:
   *  product<->oid  order by oid asc
   *
   * ManyToOne unidirectional:
   *  game<->sound    Game ist Owning Side, game.headlineSound (als property alias) und  headlineSound ist nullable
   *
   * OneToMany unidrectional (kann es nicht geben)
   *
   * ManyToMany self-referencing:
   *  game<->game  in unlockGames
   *
   * OneToMany self-referencing:
   *  parent<->child
   * 
   * ManyToOne self-referencing:
   *  children<->parent
   *
   * ein paar Custom assertions:
   *
   * assertSides($source, $target)
   *
   * ist äquivalent zu:
   * $this->assertEquals($sourceSideType, $this->relation->getSourceSide());
   * $this->assertEquals($targetSideType, $this->relation->getTargetSide());
   * 
   * asserted das der type $source (also NONE|INVERSE|OWNING) der typ von $this->relation->getSourceSide() ist, analog zu target
   *
   * 
   */
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\Relation';
    parent::setUp();
    
    $this->article = new EntityRelationMeta(new GClass('Psc\Doctrine\TestEntities\Article'), 'source');
    $this->tag = new EntityRelationMeta(new GClass('Psc\Doctrine\TestEntities\Tag'), 'target');
    $this->sound = new EntityRelationMeta(new GClass('tiptoi\Entities\Sound'), 'target');
    $this->game = new EntityRelationMeta(new GClass('tiptoi\Entities\Game'), 'source');
    $this->parent = new EntityRelationMeta(new GClass('CoMun\Entities\NavigationNode'), 'source');
    $this->parent->setAlias('Parent');
    $this->child = new EntityRelationMeta(new GClass('CoMun\Entities\NavigationNode'), 'source');
    $this->child->setAlias('Child');
    $this->unlockGame = new EntityRelationMeta(new GClass('tiptoi\Entities\Game'), 'source');
    $this->unlockGame->setAlias('UnlockGame');
    
    // ich glaube wir schreiben jeweils einen test für ein beispiel (aka many to many, etc)
    $this->relation = new EntityRelation($this->article, $this->tag, EntityRelation::MANY_TO_MANY);
  }
  
  // manyToManyDefaults
  public function testRelationDefaults() { 
    $this->assertEquals(EntityRelation::BIDIRECTIONAL, $this->relation->getDirection());
    $this->assertSides(EntityRelation::OWNING, EntityRelation::INVERSE);
  }
  
  public function testSetRelationTypeBidirectionalIgnoresOwningSideForManyToOneandOneToMany() {
    // das ist natürlich schwachsinn, weil die owning side nicht die one (source) seite sein kann
    $this->relation->setRelationType(EntityRelation::ONE_TO_MANY, EntityRelation::BIDIRECTIONAL, EntityRelation::SOURCE);
    
    // die klasse ist schlau und ignoriert unseren mist
    $this->assertSides($one = $source = EntityRelation::INVERSE, $many = $target = EntityRelation::OWNING);
  }
  
  public function testSetTypeResetsSideForManyToOneandOneToManyCorrectly() {
    $this->relation->setSourceSide(EntityRelation::OWNING);
    $this->relation->setTargetSide(EntityRelation::NONE); // oder inverse, egal
    $this->relation
      ->setDirection(EntityRelation::BIDIRECTIONAL)
      ->setType(EntityRelation::ONE_TO_MANY);
      
    $this->assertSides(EntityRelation::INVERSE, EntityRelation::OWNING); // one: inverse, many: owning

    // wenn wir manyToOne machen dreht dies owning und inverse side
    $this->relation
      ->setDirection(EntityRelation::BIDIRECTIONAL)
      ->setType(EntityRelation::MANY_TO_ONE);
    $this->assertSides(EntityRelation::OWNING, EntityRelation::INVERSE);
  }
  
  public function testSetUniDirectionalSetsInverseOwningSideToNone() {
    // unidirectionale Relations haben keine mappedBy oder inversedBy Properties:
    
    // 1. eine OneToMany, unidirectional kann auf der collection (one) - Seite nicht das "mappedBy" property haben, weil es das property auf der one seite nicht gibt
    // 2. eine ManyToOne, unidirectional ist dasselbe wie eine oneToMany unidirectional (das Property auf der one seite gibt es nicht)
    // 3. eine ManyToMany unidirectional hat kein anderes property
    // 4. eine OneToOne unidirectional hat ebenfalls kein anderes property
    // unidirectional self-referencing kann es nicht geben
    
    $this->relation->setSourceSide(EntityRelation::INVERSE);
    $this->relation->setTargetSide(EntityRelation::OWNING);
    $this->relation->setDirection(EntityRelation::UNIDIRECTIONAL);
    
    $this->assertSides(EntityRelation::NONE, EntityRelation::NONE);
  }
  
  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage Da die Relation unidirectional ist, kann die Owning Side nicht Target sein. (sonst gäbe es gar keine Owning Side)
   */
  public function testSetOwningSideUnidirectionalToTargetIsNotAllowed() {
    $this->relation->setDirection(EntityRelation::UNIDIRECTIONAL)
                   ->setOwningSide(EntityRelation::TARGET);
  }

  public function testThatSetOwningSideSetsInverseSideIfBidirectional() {
    $this->relation
      ->setDirection(EntityRelation::BIDIRECTIONAL)
      ->setOwningSide(EntityRelation::SOURCE);
    
    $this->assertEquals(EntityRelation::INVERSE, $this->relation->getTargetSide());
  }

  public function testThatsetOwningSideSetsNotInverseSideIfUnidirectional() {
    $this->relation
      ->setDirection(EntityRelation::UNIDIRECTIONAL)
      ->setOwningSide(EntityRelation::SOURCE);
    
    $this->assertEquals(EntityRelation::NONE, $this->relation->getTargetSide());
  }
  
  // gibt das property in der source class zurück
  public function testGetSourceProperty() {
    $this->assertEquals('tags', $this->relation->getSourcePropertyName());
    $this->assertInstanceof('Webforge\Types\PersistentCollectionType', $this->relation->getSourcePropertyType());
    $this->assertEquals('tag', $this->relation->getSourceParamName());
  }

  // gibt das property in der target class zurück
  public function testGetTargetProperty() {
    $this->assertEquals('articles', $this->relation->getTargetPropertyName());
    $this->assertInstanceof('Webforge\Types\PersistentCollectionType', $this->relation->getTargetPropertyType());
    $this->assertEquals('article', $this->relation->getTargetParamName());
  }
  
  /**
   * ManyToMany Bidirectional( article<->tag ) owning side: article
   * (erste Seite)
   *
   * Die Tags des Articles
   * 
   * @ORM\ManyToMany(targetEntity="Psc\Doctrine\TestEntities\Tag", inversedBy="articles")
   * @ORM\JoinTable(name="article2tag", joinColumns={@ORM\JoinColumn(name="article_id", onDelete="cascade")},
   *                                    inverseJoinColumns={@ORM\JoinColumn(name="tag_id", onDelete="cascade")})
   * @var Doctrine\Common\Collections\Collection<Psc\Doctrine\TestEntities\Tag>
   * protected $tags;
   */
  public function testManyToManyBidirectionalAnnotations() {
    $relation = new EntityRelation($this->article, $this->tag, EntityRelation::MANY_TO_MANY);
    // default ist: bidirectional, owning side: source
    $relation->setType(EntityRelation::MANY_TO_MANY);
    $relation->setDirection(EntityRelation::BIDIRECTIONAL);
    $relation->setOwningSide(EntityRelation::SOURCE);
    
    $docBlock = new DocBlock();
    $relation->addAnnotationsForSource($docBlock);
    
    $annotation = function ($name, Array $properties) {
      return Annotation::createDC($name, $properties);
    };
    
    $this->assertEquals($annotation('ManyToMany',
                                    array('targetEntity'=>'Psc\Doctrine\TestEntities\Tag',
                                          'inversedBy'=>'articles')
                                    ),
                        $this->assertHasDCAnnotation($docBlock, 'ManyToMany')
                       );
    
    //@ORM\JoinTable(name="article2tag", joinColumns={@ORM\JoinColumn(name="article_id", onDelete="cascade")},inverseJoinColumns={@ORM\JoinColumn(name="tag_id", onDelete="cascade")})
    
    $joinTable = $annotation('JoinTable', array('name'=>'article2tag',
                                                'joinColumns'=>array(
                                                  $annotation('JoinColumn', array(
                                                    'name'=>'article_id', 'onDelete'=>'cascade',
                                                  ))
                                                ),
                                                'inverseJoinColumns'=>array(
                                                  $annotation('JoinColumn', array(
                                                    'name'=>'tag_id', 'onDelete'=>'cascade',
                                                  ))
                                                )));
    $this->assertEquals($joinTable, $this->assertHasDCAnnotation($docBlock, 'JoinTable'));
  }
  
  /*
   * ManyToMany Bidirectional( article<->tag ) owning side: article
   * (andere Seite)
   *
   * Die Articles des Tags
   * 
   * @ORM\ManyToMany(targetEntity="Psc\Doctrine\TestEntities\Article", mappedBy="tags")
   * @var Doctrine\Common\Collections\Collection<Psc\Doctrine\TestEntities\Article>
   * protected $articles;
   */
  public function testManyToManyBidirectionalAnnotationsTargetSide() {
    $relation = new EntityRelation($this->tag, $this->article, EntityRelation::MANY_TO_MANY, EntityRelation::BIDIRECTIONAL, EntityRelation::TARGET); // vertauscht
    
    $docBlock = new DocBlock();
    $relation->addAnnotationsForSource($docBlock);
    
    $this->assertEquals(Annotation::createDC('ManyToMany',
                                             array('targetEntity'=>'Psc\Doctrine\TestEntities\Article',
                                                   'mappedBy'=>'tags'
                                             )
                                            ),
                        $this->assertHasDCAnnotation($docBlock, 'ManyToMany')
                       );
    
    $this->assertFalse($docBlock->hasAnnotation('Doctrine\ORM\Mapping\JoinTable'),'joinTable sollte nur auf der owning side sein');
  }

  /**
   * ManyToOne unidirectional:
   *  game<->sound    Game ist Owning Side, game.headlineSound (als property alias) und  headlineSound ist nullable
   *
   *
   * @var tiptoi\Entities\Sound
   * @ORM\ManyToOne(targetEntity="tiptoi\Entities\Sound")
   * @ORM\JoinColumn(onDelete="SET NULL")
   *
   * protected $headlineSound;
   */
  public function testManyToOneUndirectionalAnnotationsManySide() {
    $relation = new EntityRelation($this->game, $this->sound, EntityRelation::MANY_TO_ONE, EntityRelation::UNIDIRECTIONAL);
    $relation->addAnnotationsForSource($docBlock = new DocBlock());
    
    $this->assertHasRelationAnnotation('ManyToOne', array('targetEntity'=>'tiptoi\Entities\Sound', 'inversedBy'=>NULL),
                                       $docBlock);

    $joinColumn = Annotation::createDC('JoinColumn', array('onDelete'=>'SET NULL','nullable'=>false));
    $this->assertEquals($joinColumn, $this->assertHasDCAnnotation($docBlock, 'JoinColumn'));
    $this->assertFalse($docBlock->hasAnnotation('Doctrine\ORM\Mapping\JoinTable'),'kein joinTable bei ManyToOne');
  }
  
  
  public function testManyToOneUndirectionalAnnotationsOneSide() {
    //$this->assertFalse($docBlock->hasAnnotation('Doctrine\ORM\Mapping\JoinTable'),'joinTable sollte nur auf der owning side sein');
  }

  /**
   * ManyToMany self-referencing:
   *  game<->game  in unlockGames
   *
   * In game
   *
   * @ORM\ManyToMany(targetEntity="tiptoi\Entities\Game")
   * @ORM\JoinTable(name="game_unlocks", ...) // hier fehlte noch was
   * @var Doctrine\Common\Collections\Collection<tiptoi\Entities\Game>
   */
  public function testManyToManySelfReferencing() {
    $relation = new EntityRelation($this->game, $this->unlockGame, EntityRelation::MANY_TO_MANY, EntityRelation::SELF_REFERENCING);
    $relation->getJoinTable()->setName('game_unlocks');
    $relation->addAnnotationsForSource($docBlock = new DocBlock());
    
    $this->assertHasRelationAnnotation('ManyToMany', array('targetEntity'=>'tiptoi\Entities\Game', 'inversedBy'=>NULL),
                                       $docBlock
                                      );

    // eigentlich dachte ich dass doctrine schlau genug wäre, das zu ändern, macht es aber nicht, deshalb bauen wir doch
    // den jointable selbst
    $joinTable = Annotation::createDC('JoinTable', array('name'=>'game_unlocks',
                                               'joinColumns'=>array(
                                                  Annotation::createDC('JoinColumn', array(
                                                   'name'=>'game_id', 'onDelete'=>'cascade',
                                                  ))
                                               ),
                                                'inverseJoinColumns'=>array(
                                                 Annotation::createDC('JoinColumn', array(
                                                   'name'=>'unlockgame_id', 'onDelete'=>'cascade',
                                                  ))
                                                )));
    
    $this->assertEquals($joinTable, $this->assertHasDCAnnotation($docBlock,'JoinTable'));
  }
  public function testManyToManySelfReferencing_withoutAlias_withoutKnowledge() {
    $relation = new EntityRelation($this->game, $this->game, EntityRelation::MANY_TO_MANY);
    $relation->getJoinTable()->setName('game_unlocks');
    $relation->addAnnotationsForSource($docBlock = new DocBlock());
    
    $this->assertHasRelationAnnotation('ManyToMany', array('targetEntity'=>'tiptoi\Entities\Game', 'inversedBy'=>NULL),
                                       $docBlock
                                      );

    // eigentlich dachte ich dass doctrine schlau genug wäre, das zu ändern, macht es aber nicht, deshalb bauen wir doch
    // den jointable selbst
    $joinTable = Annotation::createDC('JoinTable', array('name'=>'game_unlocks',
                                               'joinColumns'=>array(
                                                  Annotation::createDC('JoinColumn', array(
                                                   'name'=>'game_id', 'onDelete'=>'cascade',
                                                  ))
                                               ),
                                                'inverseJoinColumns'=>array(
                                                 Annotation::createDC('JoinColumn', array(
                                                   'name'=>'game2_id', 'onDelete'=>'cascade',
                                                  ))
                                                )));
    
    $this->assertEquals($joinTable, $this->assertHasDCAnnotation($docBlock,'JoinTable'));
  }
  
  
  /**
   * ManyToOne self-referencing:
   *  child(ren)<->parent
   *
   * @ORM\ManyToOne(targetEntity="CoMun\Entities\NavigationNode", inversedBy="children")
   * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
   * protected $parent;
   */
  public function testOneToManySelfReferencing() {
    $relation = new EntityRelation($this->child, $this->parent, EntityRelation::MANY_TO_ONE, EntityRelation::SELF_REFERENCING, 'source');
    $relation->setJoinColumnNullable(true);
    //$relation->getJoinTable()->setName('game_unlocks');
    $relation->addAnnotationsForSource($docBlock = new DocBlock());

    $this->assertHasRelationAnnotation('ManyToOne', array('targetEntity'=>'CoMun\Entities\NavigationNode', 'inversedBy'=>'children'),
                                       $docBlock
                                      );
    

    $joinColumn = Annotation::createDC('JoinColumn', array('name'=>null, // das müsste implizit parent_id sein und kann somit weggelassen werden
                                                           'referencedColumnName'=>'id',
                                                           'onDelete'=>'SET NULL'
                                                           )
                                      );
    
    $this->assertEquals($joinColumn, $this->assertHasDCAnnotation($docBlock,'JoinColumn'));
  }

  /**
   * OneToMany self-referencing:
   * parent<->child(ren)
   *
   * @ORM\OneToMany(targetEntity="CoMun\Entities\NavigationNode", mappedBy="parent")
   * protected $children;
   */
  public function testManyToOneSelfReferencing() {
    $relation = new EntityRelation($this->parent, $this->child, EntityRelation::ONE_TO_MANY, EntityRelation::SELF_REFERENCING,'target');
    //$relation->getJoinTable()->setName('game_unlocks');
    $relation->addAnnotationsForSource($docBlock = new DocBlock());
    
    $this->assertHasRelationAnnotation('OneToMany', array('targetEntity'=>'CoMun\Entities\NavigationNode', 'mappedBy'=>'parent'),
                                       $docBlock
                                      );
  }
  

  public function testStaticCreateHelpsBuilding() {
    $relation = EntityRelation::create('Psc\Doctrine\TestEntities\Article', 'Psc\Doctrine\TestEntities\Tag', EntityRelation::MANY_TO_MANY);
    
    $this->assertEquals($this->relation, $relation);
  }
}
?>