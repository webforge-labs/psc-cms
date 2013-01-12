<?php

namespace Psc\CMS\Ajax;

use \Psc\CMS\TabsContentItem;

class TabsContentItemInfoResponse extends StandardResponse {
  
  public function __construct(\Psc\CMS\TabsContentItem $item) {
    parent::__construct(Response::STATUS_OK, Response::CONTENT_TYPE_JSON);
    
    list ($type, $id) = $item->getTabsId();
    
    $c = $this->getContent();
    $c->label = $item->getTabsLabel();
    $c->url = $item->getTabsURL();
    $c->id = $type.'-'.$id;
    $c->type = $type;
    $c->data = $item->getTabsData();
  }
}

?>