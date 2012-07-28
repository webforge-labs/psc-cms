<?php

namespace Psc\Doctrine;

use Psc\Doctrine\CollectionSynchronizer;
use Psc\Doctrine\TestEntities\Tag;
use Psc\Data\ArrayCollection;

// nicht verwirren lassen, wir benutzen von diesem test nur die testdaten + die custom assertion
require_once __DIR__.DIRECTORY_SEPARATOR.'ActionsCollectionSynchronizerTest.php';


/**
 * Ein CollectionSynchronizer für End-to-End Entity Synchronization
 *
 * anders als der PersistentCollectionSynchronizer geht dieser davon aus, dass die $toCollection komplett hydriert ist
 * @group class:Psc\Doctrine\CollectionSynchronizer
 */
class CollectionSynchronizerTest extends ActionsCollectionSynchronizerTest {
  
  public function testAcceptanceSynchronization() {
    $synchronizer = CollectionSynchronizer::createFor(
      'Psc\Doctrine\TestEntities\Article',
      'tags',
      $this->getDoctrinePackage()
    );
    $toCollection = $this->helpConvertToEntities();
    $article = $this->findArticle();
    
    $synchronizer->init($article);
    $synchronizer->process($article->getTags(), $toCollection);
    
    $this->em->flush();
    $this->em->clear();
    
    $this->assertSyncResult();
  }
  
  protected function helpConvertToEntities() {
    // wir gehen davon aus, dsas wir 2 collections haben die fertig hydriert sind
    // in unserem test ist das aber nicht so, deshalb machen wir das hier
    $hydrator = new UniqueEntityHydrator($this->em->getRepository('Psc\Doctrine\TestEntities\Tag'));
    
    return new ArrayCollection(
      $this->getToCollection(function ($id, $label) use ($hydrator) {
        $entity = $hydrator->getEntity(compact('id','label'));
        
        if ($entity === NULL) {
          $entity = new Tag($label);
        } else {
          $entity->setLabel($label); // merge nicht vergessen :)
        }
        
        return $entity;
      })
    );
  }
}
?>