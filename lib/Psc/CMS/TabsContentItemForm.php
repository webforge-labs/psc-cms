<?php

namespace Psc\CMS;

use \Psc\UI\Form as f,
    \Psc\UI\HTML as HTML
  ;

class TabsContentItemForm extends ComponentsForm {
  
  protected $item;
  
  public function __construct(TabsContentItem $item) {
    $this->item = $item;
    
    list ($type, $identifier) = $this->item->getTabsId();
    
    if ($item instanceof TabsContentActionItem) {
      $this->action = $this->item->getTabsAction();
    }
    
    parent::__construct($type.'-'.$this->action.'-form-'.$identifier);
  }
  
  public function open() {
    list ($type, $identifier) = $this->item->getTabsId();
    
    $div = parent::open();
    $div->addClass($type.'-form')->addClass('content-item-form');
    $form = $div->content->form;
    
    $form->content->identifier = f::hidden('identifier',$identifier);
    $form->content->type = f::hidden('type',$type);
    $form->content->data = f::hidden('dataJSON',json_encode($this->item->getTabsData()));
    
    return $div;
  }
}

?>