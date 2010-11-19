<?php

class FluidGrid_Form extends FluidGrid_Model {

  /**
   * 
   * @var array kann entweder ein fieldset sein oder ein einzelnes Element
   */
  protected $items;
  
  
  public function __construct (Array $items = NULL) {
    $this->items = (array) $items;
  }

  /**
   * 
   * @return FluidGrid_Model
   */
  public function getContent() {
    $c = new FluidGrid_Collection()

    foreach ($this->items as $item) {
      
      if ($item instanceof FluidGrid_HTMLElement && $item->getTag() == 'fieldset') {
        $c->addItem($item);  
      } elseif ($item instanceof FluidGrid_FormElement) {
        $c->addItem($item);
      }
      
    }
    
    $form = new FluidGrid_HTMLElement('form',$c);
    
    /* wrap in a div */
    $div = new FluidGrid_HTMLElement('div',$form,array('id'=>'forms','class'=>'block'));
    return $div;
  }
}
?>