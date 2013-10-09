<?php

namespace Psc\Doctrine;

use Psc\Doctrine\TestEntities\Tag;
use Psc\Doctrine\TestEntities\Article;

require_once __DIR__.DIRECTORY_SEPARATOR.'SynchronizerTestCase.php';

/**
 * @group class:Psc\Doctrine\PersistentCollectionSynchronizer
 */
class PersistentCollectionSynchronizerTest extends SynchronizerTestCase {
  
  public function testAcceptanceSynchronization_toCollectionAsObjectsWithId() {
    $o = function ($id, $label) {
      return (object) array(
        'id'=>$id,
        'label'=>$label
      );
    };
    
    $this->acceptanceSynchronization($o);
  }

  public function testAcceptanceSynchronization_toCollectionAsObjectsOnlyWithLabel() {
    $o = function ($id, $label) {
      return (object) array(
        'id'=>NULL,
        'label'=>$label
      );
    };
    
    $this->acceptanceSynchronization($o);
  }

  protected function acceptanceSynchronization($o) {
    $synchronizer = new PersistentCollectionSynchronizer(
      $this->getEntityMeta('Psc\Doctrine\TestEntities\Article'),
      'tags',
      new UniqueEntityHydrator($this->em->getRepository('Psc\Doctrine\TestEntities\Tag')),
      new EntityFactory($this->getEntityMeta('Psc\Doctrine\TestEntities\Tag'))
    );
    
    $article = $this->findArticle();
    $synchronizer->init($article);
    $synchronizer->process($article->getTags(), $this->getToCollection($o));
    
    $this->em->flush();
    $this->em->clear();
    
    $this->assertSyncResult();
  }
  
  
  public function testCreateFor() {
    $synchronizer = new PersistentCollectionSynchronizer(
      $this->getEntityMeta('Psc\Doctrine\TestEntities\Article'),
      'tags',
      new UniqueEntityHydrator($this->em->getRepository('Psc\Doctrine\TestEntities\Tag')),
      new EntityFactory($this->getEntityMeta('Psc\Doctrine\TestEntities\Tag'))
    );
    
    $createdSynchronizer = PersistentCollectionSynchronizer::createFor('Psc\Doctrine\TestEntities\Article', 'tags', $this->getDoctrinePackage());
    
    $this->assertEquals($createdSynchronizer, $synchronizer);
  }
}
