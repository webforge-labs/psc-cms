<?php


class FluidGrid_FormElementInputText extends FluidGrid_FormElement {

  protected $name;

  public function __construct($name, $label = NULL, $value = NULL) {
    
    $this->name = $name;
    $this->label = $label;

    $this->field = fg::h('input',$this->getLabel(),array('type'=>'text','value'=>$value));
  }
}

?>