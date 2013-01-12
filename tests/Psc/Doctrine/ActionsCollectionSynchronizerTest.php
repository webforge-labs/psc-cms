<?php

namespace Psc\Doctrine;

use Psc\Doctrine\TestEntities\Tag;
use Psc\Doctrine\TestEntities\Article;

/**
 * @group class:Psc\Doctrine\ActionsCollectionSynchronizer
 */
class ActionsCollectionSynchronizerTest extends \Psc\Doctrine\DatabaseTestCase {
  
  protected $articleId;
  protected $tags;
  protected $repository;
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\PersistanceCollectionSynchronizer';
    $this->entityClass = 'Psc\Doctrine\TestEntities\Article';
    parent::setUp();
    $this->repository = $this->em->getRepository($this->entityClass);
    $this->tagRepository = $this->em->getRepository('Psc\Doctrine\TestEntities\Article');
    
    //$this->startDebug();
    $this->loadArticleFixture();
    $this->articleId = 1;
  }
  
  protected function loadArticleFixture() {
    $articles = $this->loadTestEntities('articles');
    $article = $articles['a1'];    
    
    $this->dcFixtures->add(new TestEntities\TagsFixture());
    $this->dcFixtures->add(new TestEntities\ArticlesFixture());
    $this->dcFixtures->execute();
  }

  public function testFixture() {
    $this->tags = Helper::reindex($this->getRepository('Psc\Doctrine\TestEntities\Tag')->findAll(), 'label');
    $this->assertEquals(5, $this->tags['Wahl']->getIdentifier());
    $this->assertEquals(2, $this->tags['Demonstration']->getIdentifier());
    $this->assertEquals(1, $this->tags['Russland']->getIdentifier());
  }

  
  // toCollection ist eine Repräsentation der Objekte aus dem Universum ($this->tags) als Tag labels oder als objekt: id,label
  protected function getToCollection(\Closure $o) {
    // dieser array kommt aus dem Formular und stellt eine detachte Collection von Entities dar
    // die nur mit Ihrem Unique-Kriterium (hier der Name des Tags) versehen sind.
    return         array(
                      // fehlen:
                      // 'Protest',
      
                      // bestehend, und waren schon in der collection, mit denen geschieht nichts
                      $o(2,'Demonstration'),
                      $o(1,'Russland (RUS)'), // neues label, update
                      
                      // sind neu in die collection eingefügt worden, sind bestehend
                      $o(5, 'Wahl'),
                      // sieht auch neu aus, ist aber schon in der Datenbank (der GUI hats vergimbelt)
                      // wenn wir das hier einfügen würden, würden wir eine uniqueConstraint Exception bekommen
                      $o(NULL, 'Präsidentenwahl'),
                      
                      // nicht bestehend, ganz neu
                      $o(NULL, 'Aktuelles')
                      );
  }

  public function testAcceptanceSynchronization_toCollectionAsObjectsWithId() {
    $o = function ($id, $label) {
      return (object) compact('id','label');
    };

    $article = $this->findArticle();
    $toCollection = $this->getToCollection($o);
    $repository = $this->em->getRepository('Psc\Doctrine\TestEntities\Tag');
    
    $synchronizer = new \Psc\Doctrine\ActionsCollectionSynchronizer();
    
    $synchronizer->onHydrate(function ($toObject) use ($repository) {
        $qb = $repository->createQueryBuilder('tag');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('tag.label', ':label'),
            $qb->expr()->eq('tag.id', ':id')
          ));
        
        $qb->setParameter('id',$toObject->id)
           ->setParameter('label',$toObject->label);
      
      
      $result = $repository->deliverQuery($qb, NULL, 'singleOrNull');
      //\Psc\Doctrine\Helper::dump($result);
      return $result;
    });
    
    $synchronizer->onHash(function (Entity $tag) {
      return $tag->getIdentifier();
    });
    
    $synchronizer->onInsert(function ($o) use ($repository, $article) {
      //print "insert ".$o->label."\n";
      $tag = new Tag($o->label);
      $article->addTag($tag);
      $repository->persist($tag);
    });
    
    $synchronizer->onDelete(function (Entity $tag) use ($repository, $article) {
      //print "remove ".$tag->getLabel()."\n";
      $article->removeTag($tag);
      // wenn tag 0 verknüpfungen hat, könnte man es hier auch löschen
    });

    $synchronizer->onUpdate(function (Entity $tag, $o) use ($repository,$article) {
      //print "update ".$tag->getLabel()."\n";
      $tag->setLabel($o->label);
      $article->addTag($tag);
      $repository->persist($tag);
    });
    
    $synchronizer->process($article->getTags(), $toCollection);
    
    $this->em->flush();
    
    $this->assertSyncResult();
  }
  
  protected function assertSyncResult() {
    $this->em->clear();
    
    $article = $this->findArticle();
    $tagLabels = array();
    foreach ($article->getTags() as $tag) {
      $tagLabels[] = $tag->getLabel();
    }
    
    $expectedTags = array(
      'Demonstration',
      'Russland (RUS)',
      'Wahl',
      'Präsidentenwahl',
      'Aktuelles'
    );
    
    $this->assertArrayEquals($expectedTags, $tagLabels, Helper::debugCollectionDiff($expectedTags, $tagLabels,'tags'));
  }
  
  protected function findArticle() {
    return $this->repository->find($this->articleId);
  }
  
  public function tearDown() {
    //$this->stopDebug();
    parent::tearDown();
  }
}
?>