<?php

namespace Psc\CMS;

use Psc\Code\Event\Event;

interface ComponentsCreaterListener {

  /**
   *
   * wenn das $event als processed markiert wird, sollte der creater nicht setFormLabel() FormName(), etc machen das sollte dann
   * der Event Listener machen
   */
  public function onComponentCreated(Component $component, ComponentsCreater $creater, Event $event);
  
}
?>