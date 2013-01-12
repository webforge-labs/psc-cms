<?php

namespace Psc\UI;

use \Psc\CMS\TabsContentItem;

// TabsContentItem-ControllerCall-TabsItem
class TCIControllerCallTabsItem extends TabsItem {
  
  public function __construct(TabsContentItem $item, $todo, Array $additionalData = array()) {
    list ($type, $id) = $item->getTabsId();
    parent::__construct($type, $id, $item->getTabsLabel(), $item->getTabsData());
    
    $this->todo = 'tabs';
    $this->ctrlTodo = $todo;
    $this->data = array_merge($this->data, $additionalData);
  }
}
?>