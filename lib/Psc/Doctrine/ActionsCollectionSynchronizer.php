<?php

namespace Psc\Doctrine;

use Closure;
use Psc\Code\Event\Manager;

/**
 * Hilfs Synchronizer für den PersistentCollectionSynchronizer
 *
 * der ActionsCollectionSynchronizer ist ein HydrationCollectionSynchronizer, der ermöglicht die Actions für die verschiedenen Events mit Closures zu überschreiben
 */
class ActionsCollectionSynchronizer extends HydrationCollectionSynchronizer {
  
  protected $actions = array();
  
  public function __construct(Manager $manager = NULL) {
    $this->manager = $manager ?: new Manager();
    $this->initDefaultActions();
  }
  
  /**
   * Hydriert ein Objekt aus $toCollection in ein Objekt des Universums von $fromCollection
   *
   * muss NULL zurückgeben, wenn es kein Objekt findet, ansonsten das Objekt
   * 
   */
  public function onHydrate(Closure $action) {
    return $this->setAction('hydrateUniqueObject', $action);
  }
  
  /**
   * Ein Objekt aus $toCollection wurde hydriert und soll in die $fromCollection eingefügt werden
   * @callbackParam mixed $fromObject
   */
  public function onInsert(Closure $action) {
    return $this->setAction('insertObject', $action);
  }
  
  /**
   * Ein Objekt wurde aus $fromCollection entfernt (es wurde nicht in $toCollection hydriert)
   * 
   * delete bedeutet nur, dass das $fromObject in der Collection vorher war. Es bedeutet nicht, dass das $fromObject gelöschen werden soll (kann aber)
   * @callbackParam mixed $fromObject
   */
  public function onDelete(Closure $action) {
    return $this->setAction('deleteObject', $action);
  }
  
  /**
   *
   * update sagt nichts darüber aus, ob das Entity in der Collection war, sondern nur ob das Item im Universum existierte
   * @callbackParam mixed $toObject
   * @callbackParam mixed $fromObject
   */
  public function onUpdate(Closure $action) {
    return $this->setAction('updateObject', $action);
  }
  
  /**
   * 
   * $action erhält als ersten Parameter ein Objekt aus dem Universium von $fromCollection und muss für dieses einen eindeutigen Identifier zurückgeben, damit die Objekte indiziert werden können
   *
   * muss scalar zurückgeben!
   * 
   * @callbackParam mixed $fromObject
   */
  public function onHash(Closure $action) {
    return $this->setAction('hashObject', $action);
  }


  /**
   * Hydriert aus einer Darstellung aus $toCollection ein Objekt von $fromCollection (oder von dessen Universum)
   *
   * kann kein Objekt hydriert werden muss NULL zurückgegeben werden
   */
  public function hydrateUniqueObject($toObject, $toCollectionKey) {
    return $this->actions['hydrateUniqueObject']($toObject, $toCollectionKey);
  }
  
  /**
   * Erstellt aus einem Objekt aus $fromCollection (oder aus dessen Universum) einen Skalaren Hash
   *
   * @return scalar
   */
  public function hashObject($fromObject) {
    return $this->actions['hashObject']($fromObject);
  }
  

  // wir können entweder auf uns selbst lauschen, oder
  //public function onCollectionInsert($item, Event $event) {
  //}
  //
  //public function onCollectionUpdate($item, Event $event) {
  //}
  //
  //public function onCollectionDelete($item, Event $event) {
  //}
  
  // direkt die dispatchers abfangen (performance)
  protected function dispatchInsert($item, $eventData = array()) {
    return $this->actions['insertObject']($item);
    // wollen wir hier noch das ursprüngliche Event dispatchen?
    // hier verletzen wir gerade die ableitung von EventCollectionSynchronizer
  }

  protected function dispatchUpdate($item, $eventData = array()) {
    return $this->actions['updateObject']($eventData['from'], $eventData['to']);
  }

  protected function dispatchDelete($item, $eventData = array()) {
    return $this->actions['deleteObject']($item);
  }
  
  /**
   * Setzt die Action (ausführbarer Code) für ein bestimmtes Event
   */
  public function setAction($name, Closure $action) {
    if (!array_key_exists($name, $this->actions)) {
      throw new \InvalidArgumentException($name.' ist keine bekannte action: '.implode(',',array_keys($this->actions)));
    }
    
    $this->actions[$name] = $action;
    return $this;
  }
  
  protected function initDefaultActions() {
    $this->actions['hydrateUniqueObject'] = function ($toObject) {
      throw new \Psc\Exception('mit onHydrate() muss vorher eine Action für das hydrieren eines objekts erstellt werden');
    };
    
    $this->actions['hashObject'] = function($fromObject) {
      throw new \Psc\Exception('mit onHash() muss vorher eine Action für das hashen eines Objektes zu einem Scalar erstellt werden');
    };

    $this->actions['insertObject'] = function ($toObject) {
      throw new \Psc\Exception('mit onInsert() muss vorher eine Action für einfügen eines objektes in die Collection erstellt werden');
    };

    $this->actions['deleteObject'] = function ($toObject) {
      throw new \Psc\Exception('mit onDelete() muss vorher eine Action für das entfernen Objektes aus der Collection werden.');
    };

    $this->actions['updateObject'] = function ($fromObject, $toObject) {
      throw new \Psc\Exception('mit onUpdate() muss vorher eine Action für ein Update eines Objektes aus der Collection gesetzt werden.');
    };
  }
}
?>