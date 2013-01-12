<?php

namespace Psc\Doctrine;

use Psc\Doctrine\EntityBuilder;
use Psc\Code\Generate\GClass;
use Psc\Code\Generate\GParameter;
use Psc\Code\Generate\GMethod;
use Psc\Code\Code;

/**
 * @group entity-building
 * @group class:Psc\Doctrine\EntityBuilder
 *
 * @TODO dieser test würde viel schöner werden, wenn wir einfach einen Acceptance-Test draus machen würden.
 * wir würden ein Entity erzeugen und schauen ob all die Behaviour die wir wollen in der ClassMetadata von Doctrine ankommt
 */
class EntityRelationInterfaceBuilderTest extends \Psc\Code\Test\Base {
  
  protected $module;
  protected $entityBuilder;
  
  protected $article, $tag, $sound, $game, $oid, $product;
  protected $oid2product, $product2oid, $game2sound, $game2game, $article2tag;
  
  /**
   * Beispiele:
   *
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
   */
  public function setUp() {
    $this->module = \Psc\PSC::getProject()->getModule('Doctrine');

    $this->article = new EntityRelationMeta(new GClass('Psc\Doctrine\TestEntities\Article'), 'source');
    $this->tag = new EntityRelationMeta(new GClass('Psc\Doctrine\TestEntities\Tag'), 'target');
    $this->sound = new EntityRelationMeta(new GClass('tiptoi\Entities\Sound'), 'target');
    $this->headlineSound = new EntityRelationMeta(new GClass('tiptoi\Entities\Sound'), 'target');
    $this->headlineSound->setAlias('HeadlineSound');
    $this->game = new EntityRelationMeta(new GClass('tiptoi\Entities\Game'), 'source');
    $this->unlockGame = new EntityRelationMeta(new GClass('tiptoi\Entities\Game'), 'target');
    $this->unlockGame->setAlias('UnlockGame');
    $this->oid = new EntityRelationMeta(new GClass('tiptoi\Entities\OID'), 'source');
    $this->product = new EntityRelationMeta(new GClass('tiptoi\Entities\Product'), 'source');
    $this->person = new EntityRelationMeta(new GClass('Entities\Person'), 'source');
    $this->address = new EntityRelationMeta(new GClass('Entities\Address'), 'target');
  
    /*    
     * ManyToMany bidirectional:
     *  article<->tag  Owning Side: Article (denn da werden die Tags ja zugeordnet)
     */
    $this->article2tag = new EntityRelation($this->article, $this->tag, EntityRelation::MANY_TO_MANY, EntityRelation::BIDIRECTIONAL);
    $this->tag2article = new EntityRelation($this->tag, $this->article, EntityRelation::MANY_TO_MANY, EntityRelation::BIDIRECTIONAL);

    /*
     * ManyToOne bidirectional:
     *  oid<->product  Owning Side(geht ja auf einer Seite): OID,  product.oids ist die collectionSide
     */
    $this->oid2product = new EntityRelation($this->oid, $this->product, EntityRelation::MANY_TO_ONE, EntityRelation::BIDIRECTIONAL);
    
    /*
     * OneToMany bidrectional:
     *  product<->oid  order by oid asc
     */
    $this->product2oid = new EntityRelation($this->product, $this->oid, EntityRelation::ONE_TO_MANY, EntityRelation::BIDIRECTIONAL);
    
    /*
     * ManyToOne unidirectional:
     *  game<->sound    Game ist Owning Side, game.headlineSound (als property alias) und  headlineSound ist nullable
     */
    $this->game2sound = new EntityRelation($this->game, $this->headlineSound, EntityRelation::MANY_TO_ONE, EntityRelation::UNIDIRECTIONAL);
    $this->game2sound->setNullable(TRUE);

    /**    
     * ManyToMany unidirectional:
     *
     * Jede Person hat eine Adresse, aber aus Datenschutzgründen wollen wir nicht wissen, wer in einem Haus zusammen lebt
     */
    $this->person2address = new EntityRelation($this->person, $this->address, EntityRelation::MANY_TO_MANY, EntityRelation::UNIDIRECTIONAL);
    // die Gegen-Seite gibt es dann nicht
    //$this->address2person = new EntityRelation($this->person, $this->address, EntityRelation::MANY_TO_MANY, EntityRelation::UNIDIRECTIONAL);
    
    /*
     * ManyToMany self-referencing:
     *  game<->game  in unlock_games
     */
    $this->game2unlockGame = new EntityRelation($this->game, $this->unlockGame, EntityRelation::MANY_TO_MANY); // checkt dann self referencing
    $this->game2unlockGame->getJoinTable()->setName('unlock_games');
  }
  
  /**
   *
   */
  public function testBidirectionalCollectionSideGetsCollectionInterfaceMethods() {
    // OneToMany
    $productClass = $this->buildRelation($this->product2oid);
    
    // die setters/getters kommen aus dem EntityBuilder
    
    $this->assertHasMethod('getOIDs', $productClass);
    $this->assertHasMethod('setOIDs', $productClass);
    $this->assertHasMethod('addOID', $productClass);
    $this->assertHasMethod('removeOID', $productClass);
    $this->assertHasMethod('hasOID', $productClass);
    $this->assertHasMethod('__construct', $productClass);
    $this->assertContains('$this->oids = new \Psc\Data\ArrayCollection()', $productClass->getMethod('__construct')->php());
    
    // ManyToMany
    $articleClass = $this->buildRelation($this->article2tag);
    $this->assertHasMethod('getTags', $articleClass);
    $this->assertHasMethod('setTags', $articleClass);
    $this->assertHasMethod('addTag', $articleClass);
    $this->assertHasMethod('removeTag', $articleClass);
    $this->assertHasMethod('hasTag', $articleClass);
    $this->assertContains('$this->tags = new \Psc\Data\ArrayCollection()', $articleClass->getMethod('__construct')->php());
    
    // ManyToMany, self
    $gameClass = $this->buildRelation($this->game2unlockGame);
    $this->assertHasMethod('getUnlockGames', $gameClass);
    $this->assertHasMethod('setUnlockGames', $gameClass);
    $this->assertHasMethod('addUnlockGame', $gameClass);
    $this->assertHasMethod('removeUnlockGame', $gameClass);
    $this->assertHasMethod('hasUnlockGame', $gameClass);
    $this->assertContains('$this->unlockGames = new \Psc\Data\ArrayCollection()', $gameClass->getMethod('__construct')->php());
  }
  
  public function testBidirectionalManyToOneHasNotAddAndRemoveMethods() {
    // weil wir ja auf der many seite einen setter haben (oid::setProduct() wäre oid::addProduct() irreführend)
    $oidClass = $this->buildRelation($this->oid2product);
    
    $this->assertNotHasMethod('addProduct', $oidClass);
    $this->assertNotHasMethod('removeProduct', $oidClass);
    $this->assertNotHasMethod('hasProduct', $oidClass);
  }

  public function testCollectionSideHasNotAddOrRemoveXXXWhenRelationIsUnidirectional() {
    // many2many unidirectional
    $personClass = $this->buildRelation($this->person2address);
    
    $this->assertCallsNot('addAddress', $this->assertHasMethod('addAddress', $personClass));
    $this->assertCallsNot('removeAddress', $this->assertHasMethod('removeAddress', $personClass));
  }

  public function testHasXXXHasParameterWithHint() {
    $articleClass = $this->buildRelation($this->article2tag);
    $this->assertSingleParameter('tag', 'Psc\Doctrine\TestEntities\Tag', $this->assertHasMethod('hasTag', $articleClass));
  }

  public function testAddXXXXHasSingleParameter() {
    $gameClass = $this->buildRelation($this->game2unlockGame);
    $this->assertSingleParameter('unlockGame', 'tiptoi\Entities\Game', $this->assertHasMethod('addUnlockGame', $gameClass));
  }

  public function testRemoveXXXXHasSingleParameter() {
    $productClass = $this->buildRelation($this->product2oid);
    $this->assertSingleParameter('oid', 'tiptoi\Entities\OID', $this->assertHasMethod('removeOID', $productClass));
  }

  public function testAddAddsToOtherSide() {
    $articleClass = $this->buildRelation($this->article2tag);
    $this->assertCalls('addArticle', $this->assertHasMethod('addTag', $articleClass));
  }

  public function testRemoveAddsToOtherSide() {
    $articleClass = $this->buildRelation($this->article2tag);
    $this->assertCalls('removeArticle', $this->assertHasMethod('removeTag', $articleClass));
  }

  
  public function testSetterInOneToManyAddsNOTBecauseInverseSide() {
    //$this->markTestIncomplete('@TODO der setter der MANY seite in einem ManyToOne muss add auf der one seite ausführen');
    $productClass = $this->buildRelation($this->product2oid); // OneToMany
    
    $addOID = $this->assertHasMethod('addOID', $productClass);
    $this->assertCallsNot('addProduct', $addOID);
  }
  
  public function testSetterInManyToOneAddsToOneSideCollectionInSetter() {
    //$this->markTestIncomplete('@TODO der setter der MANY seite in einem ManyToOne muss add auf der one seite ausführen');
    $oidClass = $this->buildRelation($this->oid2product);
    
    $addProduct = $this->assertHasMethod('setProduct', $oidClass);
    $this->assertCalls('addOID', $addProduct);
  }

  public function testRelationInterface_SetterInManyToOneRemovesInOneSideCollection() {
    $this->markTestIncomplete('@TODO should it?');
  }
  
  public function testSetterInManyToOneAllowsNULLWhenNullableisTRUEAndUniDirectionalOrBidirectional() {
    $this->assertTrue($this->game2sound->isTargetNullable());
    $gameClass = $this->buildRelation($this->game2sound); // dsa ist gleichzeitig ein guter test ob injected wird obwohl unidirectional
    
    $setHeadlineSound = $this->assertHasMethod('setHeadlineSound', $gameClass);
    $soundParam = $this->assertSingleParameter('headlineSound', 'tiptoi\Entities\Sound', $setHeadlineSound);
    $this->assertTrue($soundParam->isOptional(), 'headlineSound ist nicht optional');
    $this->assertSame(NULL, $soundParam->getDefault());
  }
  
  protected function buildRelation(EntityRelation $relation) {
    //$builder = new EntityRelationInterfaceBuilder($relation);
    $gClass = clone $relation->getSource()->getGClass();
    $gClass->setClassName('Compiled'.$gClass->getClassName());
    
    // da wir ein paar properties vom entityBuilder brauchen und der EntityBuilder
    // den EntityRelationInterfaceBuilder benutzt, ist dies ein indirekter acceptance test
    // es ist auch möglich hier den builder direkt zu testen, jedoch müssen wir dann ein bißchen funktionalität auslassen
    // außerdem wird das relation interface von den entitybuilder tests gar nicht abgedeckt
    
    $builder = new EntityBuilder($gClass);
    $builder->buildRelation($relation);
    
    return $gClass;
  }
  
  protected function assertCallsNot($part, GMethod $method) {
    $this->assertNotContains('->'.$part, $method->php(), $method->getName().' ruft '.$part.' auf (soll es aber nicht).');
  }

  protected function assertCalls($part, GMethod $method) {
    $this->assertContains('->'.$part, $method->php(), $method->getName().' ruft '.$part.' auf.');
  }
  
  protected function assertHasMethod($name, GClass $gClass) {
    $this->assertTrue($gClass->hasMethod($name), sprintf("GClass '%s' hat Methode '%s' nicht. (Vorhanden %s)", $gClass->getClassName(), $name, \Psc\FE\Helper::listObjects($gClass->getMethods(), ',', 'name')));
    return $gClass->getMethod($name);
  }

  protected function assertNotHasMethod($name, GClass $gClass) {
    $this->assertFalse($gClass->hasMethod($name), sprintf("GClass '%s' hat Methode '%s' (soll sie aber nicht haben).", $gClass->getClassName(), $name));
  }

  protected function assertSingleParameter($name, $FQN, GMethod $method) {
    $this->assertCount(1, $method->getParameters(), 'Methode '.$method->getName().' hat nicht genau einen Parameter');
    $this->assertTrue($method->hasParameter($name), 'Methode '.$method->getName().' hat nicht den Parameter: '.$name.' es sind vorhanden: '.\Psc\FE\Helper::listObjects($method->getParameters(),',','name'));
    
    $param = $method->getParameter($name);
    $this->assertEquals($FQN, $param->getHint()->getFQN(), 'Parameter Hint für '.$name.' in '.$method->getName());
    return $param;
  }
}
?>