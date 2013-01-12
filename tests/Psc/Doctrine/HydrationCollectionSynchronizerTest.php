<?php

namespace Psc\Doctrine;

use Psc\Data\ArrayCollection;
use Psc\Code\Event\Event;

/**
 * @group class:Psc\Doctrine\HydrationCollectionSynchronizer
 */
class HydrationCollectionSynchronizerTest extends \Psc\Code\Test\Base implements EventCollectionSynchronizerListener {
  
  protected $dispatchedInserts = array();
  protected $dispatchedDeletes = array();
  protected $dispatchedUpdates = array();
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\HydrationCollectionSynchronizer';
    parent::setUp();
    
    // unsere Datenbank
    $this->tags = $this->loadTestEntities('tags');
  }
  
  // FromCollection ist eine Collection eines Teils des Universums ($this->tags)
  protected function getFromCollection() {
    
    return new ArrayCollection(array(
      $this->tags['t3'], // protest
      
      $this->tags['t2'], // demonstration
      $this->tags['t1'], // russland
    ));
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
                      $o(1,'Russland'),
                      
                      // sind neu in die collection eingefügt worden, sind bestehend
                      $o(5, 'Wahl'),
                      // sieht auch neu aus, ist aber schon in der Datenbank (der GUI hats vergimbelt)
                      // wenn wir das hier einfügen würden, würden wir eine uniqueConstraint Exception bekommen
                      $o(NULL, 'Präsidentenwahl'),
                      
                      // nicht bestehend, ganz neu
                      $o(NULL, 'Aktuelles')
                      );
  }

  protected function initResult($o) {
    $this->deletes = array(
      $this->tags['t3']
    );
    
    $this->updates = array(
      $this->tags['t2'],
      $this->tags['t1'],
      $this->tags['t4'], // wahl
      $this->tags['t5'], // Präsidentenwahl
    );
    
    $this->inserts = array(
      $o(NULL,'Aktuelles')
    );
  }

  // $item ist indemFalle ein String oder ein stdClass-Object
  public function onCollectionInsert($item, Event $event) {
    $this->dispatchedInserts[] = $item;
  }
  
  public function onCollectionUpdate($item, Event $event) {
    $this->dispatchedUpdates[] = $item;
  }
  
  public function onCollectionDelete($item, Event $event) {
    $this->dispatchedDeletes[] = $item;
  }
  

  public function testSynchronizationWithTagLabels() {
    $o = function ($id, $name) {
      return $name;
    };
    $this->initResult($o);
    
    $synchronizer = new TagNamesSynchronizer();
    $synchronizer->tags = $this->tags;
    $synchronizer->subscribe($this);
    $synchronizer->process($this->getFromCollection(), $this->getToCollection($o));
    $this->assertSyncResult();
  }
  

  public function testSynchronizationWithTagObjects() {
    $o = function ($id, $label) {
      return (object) compact('id','label');
    };
    $this->initResult($o);
    
    $synchronizer = new TagsSynchronizer();
    $synchronizer->tags = $this->tags;
    $synchronizer->subscribe($this);
    $synchronizer->process($this->getFromCollection(), $this->getToCollection($o));
    
    $this->assertSyncResult();
  }

  protected function assertSyncResult() {
    $c = function ($a) { return new ArrayCollection($a); };
    $this->assertEntityCollectionEquals($c($this->updates), $c($this->dispatchedUpdates), 'updates');
    $this->assertEntityCollectionEquals($c($this->deletes), $c($this->dispatchedDeletes), 'deletes');
    
    $this->assertEquals($this->inserts, $this->dispatchedInserts);
  }
}

class TagNamesSynchronizer extends HydrationCollectionSynchronizer {
  
  public $tags;
  
  public function hydrateUniqueObject($toObject, $toCollectionKey) {
    $tagLabel = $toObject;
    
    foreach ($this->tags as $tag) {
      if ($tag->getLabel() === $tagLabel) {
        return $tag;
      }
    }
    
    return NULL;
  }
  
  public function hashObject($fromObject) {
    return $fromObject->getId();
  }
}


class TagsSynchronizer extends HydrationCollectionSynchronizer {
  
  public $tags;
  
  
  public function hydrateUniqueObject($toObject, $toCollectionKey) {
    foreach ($this->tags as $tag) {
      if ($tag->getId() === $toObject->id ||
          $tag->getLabel() === $toObject->label) {
        
        return $tag;
      }
    }
    
    return NULL;
  }

  public function hashObject($fromObject) {
    return $fromObject->getId();
  }
}
?>