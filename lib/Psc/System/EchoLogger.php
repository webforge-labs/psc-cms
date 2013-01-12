<?php

namespace Psc\System;

use Psc\Code\Event\Subscriber;
use Psc\Code\Event\Event;

class EchoLogger extends BufferLogger implements Subscriber {
  
  protected $out = NULL;
  
  public function setUpListener() {
    $this->manager->bind($this, 'Logger.Written');
    parent::setUpListener();
  }
  
  public function trigger(Event $e) {
    $this->flush();
  }
  
  public function flush() {
    $flush = $this->toString();
    print $flush;
    $this->out .= $flush;
    flush();
    $this->reset();
  }
  
  public function getOut() {
    return $this->out;
  }
}
?>