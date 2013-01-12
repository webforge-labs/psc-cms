<?php

namespace Psc\Code\Event;

use Closure;

/**
 * Ein CallbackSubscriber ist ein einfacher Weg ein Subscriber zu werden ohne eine neue Klasse erstellen zu müssen
 *
 * wenn der Subscriber ein Event erhält wird der Callback aufgerufen mit dem ersten Parameter als Event
 */
class CallbackSubscriber extends \Psc\Code\Callback implements Subscriber {
  
  /**
   * Ein Filter für Events die nicht durch den Identifier eindeutig bestimmt werden können
   *
   * gibt der die Closure FALSE zurück wird call() nicht aufgerufen
   * analog zu: array_filter()
   * @var Closure
   */
  protected $eventFilter;
  
  /**
   * Ein Helper der die (dynamischen)-Argumente für den Callback erstellt
   *
   * 1. Parameter das Event
   * Rückgabe: array $args wie sie an call() übergeben werden
   * @var Closure
   */
  protected $argsMapper;
  
  public function trigger(Event $event) {
    // check if filter matches
    if (isset($this->eventFilter) && !$this->eventFilter->__invoke($event)) {
      return;
    }
    
    $args = array($event);
    
    if (isset($this->argsMapper)) {
      $args = $this->argsMapper->__invoke($event);
    }
    
    $this->call($args);
  }
  
  public function setEventFilter(Closure $filter = NULL) {
    $this->eventFilter = $filter;
    return $this;
  }

  public function setArgumentsMapper(Closure $function = NULL) {
    $this->argsMapper = $function;
    return $this;
  }
}
?>