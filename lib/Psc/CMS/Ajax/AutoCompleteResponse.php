<?php

namespace Psc\CMS\Ajax;

use \Psc\CMS\TabsContentItem,
    \Closure
  ;

class AutoCompleteResponse extends StandardResponse {
  
  protected $labelHook;
  
  public function __construct(Array $items, Closure $labelHook = NULL) {
    parent::__construct(Response::STATUS_OK, Response::CONTENT_TYPE_JSON);
    $this->labelHook = $labelHook;

    $acData = array();
    /* kopiert aus FormItemAutoComplete */
    foreach ($items as $item) {
      if ($item instanceof \Psc\CMS\TabsContentItem) {
        list($type,$id) = $item->getTabsId();
        $acData[] = array('label'=>(isset($labelHook) ? $labelHook($item) : $item->getTabsLabel()),
                          'value'=>$id,
                          
                          // die sind optional für uns und stören AC nicht
                          'identifier'=>$id,
                          'type'=>$type,
                          'data'=>$item->getTabsData()
                      );
      } elseif ($item instanceof \Psc\Form\Item) {
        $acData[] = array('label'=>(isset($labelHook) ? $labelHook($item) : $item->getFormLabel()),
                          'value'=>$item->getFormValue(),
                      );
      } else {
        throw new \Psc\Exception('Nur \Psc\Form\Item benutzbar in ArrayCollection für Tags');
      }
    }

    $c = $this->getContent();
    $c->items = $acData;
  }
}

?>