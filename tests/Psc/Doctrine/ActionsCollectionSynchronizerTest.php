<?php

namespace Psc\Doctrine;

use Psc\Doctrine\TestEntities\Tag;
use Psc\Doctrine\TestEntities\Article;

require_once __DIR__.DIRECTORY_SEPARATOR.'SynchronizerTestCase.php';

/**
 * @group class:Psc\Doctrine\ActionsCollectionSynchronizer
 */
class ActionsCollectionSynchronizerTest extends SynchronizerTestCase {
  
  public function testFixture() {
    $this->tags = Helper::reindex($this->getRepository('Psc\Doctrine\TestEntities\Tag')->findAll(), 'label');
    $this->assertEquals(5, $this->tags['Wahl']->getIdentifier());
    $this->assertEquals(2, $this->tags['Demonstration']->getIdentifier());
    $this->assertEquals(1, $this->tags['Russland']->getIdentifier());
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
  
  public function tearDown() {
    //$this->stopDebug();
    parent::tearDown();
  }
}
