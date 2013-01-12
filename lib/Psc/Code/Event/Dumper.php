<?php

namespace Psc\Code\Event;

use \Psc\Code\Event\Dispatcher;

class Dumper extends \Psc\SimpleObject implements \Psc\Code\Event\Subscriber {
  
  /**
   * Event[]
   */
  protected $dumps;
  
  public function __construct(Dispatcher $dispatcher) {
    $dispatcher->getManager()->bind($this, NULL);
    $this->reset();
  }
  
  public function trigger(Event $event) {
    $this->dump($event);
  }
  
  public function getCount() {
    return count($this->dumps);
  }
  
  public function reset() {
    $this->dumps = array();
    return $this;
  }
  
  public function dump(Event $event) {
    $this->dumps[] = $event;
    return $this;
  }
  
  /**
   * @return array
   */
  public function getDumps() {
    return $this->dumps;
  }
  
  /**
   * @return array
   */
  public function getEvents() {
    return $this->dumps;
  }
}
?>