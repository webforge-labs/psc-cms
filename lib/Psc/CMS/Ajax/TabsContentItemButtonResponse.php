<?php

namespace Psc\CMS\Ajax;

use \Psc\CMS\TabsContentItem,
    \Psc\UI\TabsButtonItem
;

class TabsContentItemButtonResponse extends TabsContentItemInfoResponse {
  
  public function __construct(\Psc\CMS\TabsContentItem $item) {
    parent::__construct($item);
    
    $c = $this->getContent();
    $c->button = new TabsButtonItem($item);
  }
  
  public function export() {
    $c = $this->getContent();
    $c->options = $c->button->getWidgetOptions();
    $c->html = (string) $c->button->html();
    
    return parent::export();
  }
}

?>