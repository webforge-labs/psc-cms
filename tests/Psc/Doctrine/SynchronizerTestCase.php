<?php

namespace Psc\Doctrine;

abstract class SynchronizerTestCase extends \Psc\Doctrine\DatabaseTestCase {

  protected $articleId;
  protected $tags;
  protected $repository;

  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\PersistanceCollectionSynchronizer';
    $this->entityClass = 'Psc\Doctrine\TestEntities\Article';

    $this->fixtures  = array(
      new TestEntities\TagsFixture(),
      new TestEntities\ArticlesFixture()
    );

    parent::setUp();
    $this->repository = $this->em->getRepository($this->entityClass);
    $this->tagRepository = $this->em->getRepository('Psc\Doctrine\TestEntities\Article');
    
    $this->articleId = 1;
  }

  protected function setUpModule($module) {
    $this->loadEntity('Psc\Doctrine\TestEntities\Tag', $module);
    $this->loadEntity($this->entityClass, $module);
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
}