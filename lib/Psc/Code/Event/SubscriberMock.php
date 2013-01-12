<?php

namespace Psc\Code\Event;

class SubscriberMock implements Subscriber {
  public function trigger(Event $event) {}
}

?>