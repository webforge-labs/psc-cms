<?php

namespace Psc\UI\Component;

class SmallIntegerField extends \Psc\UI\Component\IntegerField {
  
  protected $size = 2;

  public function getInnerHTML() {
    return
      parent::getInnerHTML()
        ->setStyle('width',($this->size+1).'em')
        ->setAttribute('maxlength',$this->size)
    ;
  }
}
?>