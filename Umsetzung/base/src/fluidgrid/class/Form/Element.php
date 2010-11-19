<?php


class FluidGrid_FormElement extends FluidGrid_Model {

  /**
   * 
   * @var string
   */
  protected $label;

  /**
   * 
   * @var FluidGrid_HTMLElement
   */
  protected $field;

  public function __construct($label, FluidGrid_HTMLElement $field) {
    $this->label = $label;
    $this->field = $field;
  }
  
  
  public static function factory() {
    $args = func_get_args();
    $argsNum = count($args);
    
    /* textfeld mit label */
    if ($argsNum == 2 && is_string($args[0]) && is_string($args[1])) {
      return new FluidGrid_FormElementInputText($args[0],$args[1]);
    }
  }


  public function getLabel() {
    if (!isset($this->label) && isset($this->name))
      return ucfirst($this->name);
    
    return $this->label;
  }


  public function getContent() {

    $label = fg::h('label', $this->getLabel());
    $field = $this->getField();

    if ($field->getId() == NULL) {
      $field->generateId($field->getName());
    }

    $label->setAttribute('for',$field->getId());

    return new fg::h('p', fg::c($label,$field) );
  }
}

?>