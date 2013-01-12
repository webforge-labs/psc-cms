<?php

namespace Psc\Doctrine;

use Psc\Data\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Psc\Code\Event\Manager;
use Psc\Code\Event\Event;
use Psc\Code\Event\CallbackSubscriber;
use Psc\Code\Callback;
use Closure;

/**
 * Ein GenericCollectionSynchronizer synchronisiert 2 fertig hydrierte Collections anhand einer vergleichstrategie
 *
 * er triggert Events für inserts/updates/deletes von toCollection für fromCollection
 * anders als der PersistentCollectionSynchronizer oder HydrationSynchronizer erwartet er die 2te Collection fertig hydriert
 *
 * Beim Synchronisieren haben wir 2 Collections zu betrachten:
 * $fromCollection - die Collection ist die vorhandene / gespeicherte Collection
 * $toCollection - die zweite ist das update für die Collection
 *
 * zuerst müssen wir natürlich $toCollection aus verschiedenen Daten erstellen (Formulare, Requests, statische Daten, Caches, etc). Dabei ist wichtig, dass Items, die in $fromCollection vorkommen auch identisch zu items in $toCollection sind.
 *
 * durch die items in $toCollection können folgende Ereignisse auftreten:
 *   - onInsert (das Entity existiert nicht in $fromCollection)
 *   - onUpdate (das Entity existiert in der $fromCollection)
 *   - onDelete (das Entity fehlt in $toCollection und existiert in $fromCollection)
 *
 * um diese Ereignisse verarbeiten zu können, müssen wir diese 3 Mengen berechnen
 *   - computeCollectionSets - list(Collection $inserted, Collection $updated, Collection $deleted)
 *
 * dies ist eine Low-Level-Klasse und hat deshalb überhaupt keine Convenience-Methoden
 */
class GenericCollectionSynchronizer extends EventCollectionSynchronizer implements EventCollectionSynchronizerListener {
  
  /**
   * Callbacks für den self-listener
   *
   * @var Closure[]
   */
  protected $callbacks = array();
  
  
  public function __construct(Array $callbacks = array(),Manager $manager = NULL) {
    parent::__construct($manager);
    $this->callbacks = $callbacks;
    
    $this->subscribe($this); // jaja, es geht halt schneller, wenn wir auf das eventslistening verzichten und dispatchxxx() überschreiben
  }
  
  public function process(ArrayCollection $fromCollection, ArrayCollection $toCollection) {
    list ($inserts, $updates, $deletes) = $this->computeCUDSets($fromCollection, $toCollection);
    
    $this->dispatch($inserts, $updates, $deletes);
  }
  
  public function dispatch(Collection $inserts, Collection $updates, Collection $deletes) {
    // das ist ja alles ganz schön, macht aber für entities keinen sinn weil uniqueconstraintexception zu fangen
    // würde bedeuten für jedes entity flush() aufzurufen
    
    foreach ($inserts as $item) {
      try {
        $this->manager->dispatchEvent(self::EVENT_INSERT, array('item'=>$item), $this);
      } catch (UniqueConstraintException $e) {
        $this->dispatchUniqueConstraintEventFromException($e, $item);
      }
    }

    foreach ($updates as $item) {
      try {
        $this->manager->dispatchEvent(self::EVENT_UPDATE, array('item'=>$item), $this);
      } catch (UniqueConstraintException $e) {
        $this->dispatchUniqueConstraintEventFromException($e, $item);
      }
    }

    foreach ($deletes as $item) {
      $this->manager->dispatchEvent(self::EVENT_DELETE, array('item'=>$item), $this);
    }
  }

  /**
   * Berechnet die Updates / Inserts / Deletes zwischen den beiden Collections
   *
   * wird keine $compareFunction angegeben, werden die Elemente der Collection als Objekte behandelt
   * wird keine $uniqueHashFunction angegeben werden diese mit spl_object_hash() gehashed
   * @return list($inserts, $updates, $deletes)
   */
  public function computeCUDSets(ArrayCollection $fromCollection, ArrayCollection $toCollection, $compareFunction = NULL, $uniqueHashFunction = NULL) {
    if (!isset($compareFunction)) {
      $compareFunction = ArrayCollection::COMPARE_OBJECTS;
    }
    
    return $fromCollection->computeCUDSets($toCollection, $compareFunction, $uniqueHashFunction);
  }
  
  public function onInsert(Closure $callback) {
    $this->callbacks['insert'] = $callback;
    return $this;
  }
  
  public function onUpdate(Closure $callback) {
    $this->callbacks['update'] = $callback;
    return $this;
  }

  public function onDelete(Closure $callback) {
    $this->callbacks['delete'] = $callback;
    return $this;
  }


  // dies sind nur alias weil das GenericCollectionListener Interface natürlich nicht onInsert blocken sollte
  public function onCollectionInsert($item, Event $event) {
    return $this->onEvent('insert', $item, $event);
  }
  
  public function onCollectionUpdate($item, Event $event) {
    return $this->onEvent('update', $item, $event);
  }
  
  public function onCollectionDelete($item, Event $event) {
    return $this->onEvent('delete', $item, $event);
  }

  protected function onEvent($type, $item, Event $event) {
    if (array_key_exists($type,$this->callbacks)) {
      $cb = $this->callbacks[$type];
      return $cb($item, $event);
    }
  }
}
?>