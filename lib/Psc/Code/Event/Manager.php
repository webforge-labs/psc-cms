<?php

namespace Psc\Code\Event;

class Manager extends \Psc\Object {
  
  protected $subscribers = array();
  
  /**
   * Dispatched ein Event
   * @chainable
   */
  public function dispatch(Event $event) {
    
    foreach ($this->getSubscribers($event) as $subscriber) {
      $subscriber->trigger($event);
    }
    
    return $this;
  }
  
  /**
   * Dispatched ein (nicht erstelltes) Event
   *
   * $manager->dispatchEvent('Psc.SourceFileChanged', array('file'=>'D:\www\psc-cms\Umsetzung\base\src\PSC.php'), $this);
   *
   * ist dasselbe wie
   * $event = new Event('Psc.SourceFileChanged',$this);
   * $manager->dispatch($event);
   *
   * anders als dispatch() gibt dies das erstellte Event zurück (dieses ist dann aber schon dispatched)
   * @return Psc\Code\Event\Event
   */
  public function dispatchEvent($eventName, $eventData = NULL, $target = NULL) {
    $event = new Event($eventName,$target);
    $event->setData($eventData);
    $this->dispatch($event);
    
    return $event;
  }
  
  /**
   * Bindet den Subscriber an einen Event(Typ)
   *
   * Wenn das Event dann dispatched wird, wird der subscriber mit trigger() benachrichtigt
   * @param $eventIdentifier kann NULL sein für alle Event-Typen
   */
  public function bind(Subscriber $subscriber, $eventIdentifier) {
    if ($eventIdentifier instanceof Event) {
      $eventIdentifier = $eventIdentifier->getIdentifier();
    }
    
    if (!isset($this->subscribers[$eventIdentifier]))
      $this->subscribers[$eventIdentifier] = array();
      
    $this->subscribers[$eventIdentifier][] = $subscriber;
    
    return $this;
  }
  
  /**
   * @return array
   */
  public function getSubscribers(Event $event) {
    $directSubscribers = array_key_exists($i = $event->getIdentifier(), $this->subscribers) ? $this->subscribers[$i] : array();
    $nonSpecificSubscribers = array_key_exists(NULL, $this->subscribers) ? $this->subscribers[NULL] : array();
    return array_merge($directSubscribers, $nonSpecificSubscribers);
  }
}
?>