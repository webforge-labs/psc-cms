<?php

namespace Psc\Doctrine;

use Psc\Code\Event\Manager;
use Psc\Code\Event\Event;
use Psc\Code\Event\CallbackSubscriber;

/**
 * Der EventCollectionSynchronizer ist eine Hilfs BasisKlasse für alle Synchronizer die ihre Synchronization mit Events steuern
 *
 * der EventCollectionSynchronizer bestitzt keine eigene Synchronisations-Logik
 */
abstract class EventCollectionSynchronizer extends \Psc\SimpleObject implements \Psc\Code\Event\Dispatcher {
  
  const EVENT_UPDATE = 'Psc\Doctrine\GenericCollectionSynchronizer.Update';
  const EVENT_INSERT = 'Psc\Doctrine\GenericCollectionSynchronizer.Insert';
  const EVENT_DELETE = 'Psc\Doctrine\GenericCollectionSynchronizer.Delete';
  const EVENT_DUPLICATE_KEY = 'Psc\Doctrine\GenericCollectionSynchronizer.DuplicateKey';
  
  
  protected $manager;
  
  public function __construct(Manager $manager = NULL) {
    $this->manager = $manager ?: new Manager();
  }
  
  protected function dispatchUniqueConstraintEventFromException(UniqueConstraintException $e, $item) {
    $this->manager->dispatchEvent(self::EVENT_DUPLICATE_KEY, array('item'=>$item,
                                                                   'exception'=>$e,
                                                                   'identifier'=>$e->duplicateIdentifier
                                                                  ), $this);
  }
  
  public function subscribe(EventCollectionSynchronizerListener $listener) {
    $this->manager->bind(
      new CallbackSubscriber(
        function ($event) use ($listener) {
          $listener->onCollectionInsert($event->getData()->item, $event);
        }
      ),
      self::EVENT_INSERT
    );

    $this->manager->bind(
      new CallbackSubscriber(
        function ($event) use ($listener) {
          $listener->onCollectionUpdate($event->getData()->item, $event);
        }
      ),
      self::EVENT_UPDATE
    );

    $this->manager->bind(
      new CallbackSubscriber(
        function ($event) use ($listener) {
          $listener->onCollectionDelete($event->getData()->item, $event);
        }
      ),
      self::EVENT_DELETE
    );

    $this->manager->bind(
      new CallbackSubscriber(
        function ($event) use ($listener) {
          $listener->onCollectionDuplicateKey($event->getData()->item, $event->getData()->identifier, $event);
        }
      ),
      self::EVENT_DUPLICATE_KEY
    );
  }

  /* Hilfsfunktionen für ableitende Klassen */  
  
  protected function dispatchInsert($item, $eventData = array()) {
    return $this->manager->dispatchEvent(self::EVENT_INSERT, array_merge(array('item'=>$item), $eventData), $this);
  }

  protected function dispatchUpdate($item, $eventData = array()) {
    return $this->manager->dispatchEvent(self::EVENT_UPDATE, array_merge(array('item'=>$item), $eventData), $this);
  }

  protected function dispatchDelete($item, $eventData = array()) {
    return $this->manager->dispatchEvent(self::EVENT_DELETE, array_merge(array('item'=>$item), $eventData), $this);
  }

  
  /**
   * @return Psc\Code\Event\Manager
   */
  public function getManager() {
    return $this->manager;
  }
}
?>