<?php

namespace Psc\Doctrine;

use Psc\Code\Event\Event;

interface EventCollectionSynchronizerListener {
  
  public function onCollectionInsert($item, Event $event);
  
  public function onCollectionUpdate($item, Event $event);
  
  public function onCollectionDelete($item, Event $event);

}
?>