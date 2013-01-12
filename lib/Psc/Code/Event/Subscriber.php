<?php

namespace Psc\Code\Event;

interface Subscriber {
  
  public function trigger(Event $event);
  
}