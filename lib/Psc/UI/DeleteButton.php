<?php

namespace Psc\UI;

use \Psc\JS\Lambda,
    \Psc\CMS\TabsContentItem
  ;

class DeleteButton extends Button {
  
  protected $deleteHint = 'Zum Löschen müssen erst alle anderen Verknüpfungen gelöst werden.';
  
  protected $buttonItem;
  
  protected $hint;
  
  public function __construct(TabsContentItem $item, $label = 'Löschen') {
    parent::__construct($label);
    
    $this->click(new Lambda("function (e) {
      e.preventDefault();
      $.pscUI('form','deleteItem',$(this));
    }"));
    
    $this->setLeftIcon('trash');
    $this->setRightIcon('info');
    
    $this->buttonItem = new TabsButtonItem($item);
    $this->buttonItem->ctiWidget($this);
  }
  
  public function disable() {
    parent::disable();
    
    $this->hint = Form::hint($this->deleteHint)->templatePrepend('<br />');
    $this->after($this->hint);
  }
  
  public function enable() {
    parent::enable();
    
    $this->hint->setAttribute('display','none');
  }
}
?>