<?php

class FluidGrid_Collection extends FluidGrid_Model {
  
  protected $items = array();

  protected $modelClass = 'FluidGrid_Model';

  protected $indentItems = 0;
  
  public function __construct() {
    $args = func_get_args();
    
    $this->addItems($args);
  }

  public static function factory() {
    $args = func_get_args();
    return new FluidGrid_Collection($args);
  }

  public function addItems(Array $items) {
    foreach ($items as $item) {
      if (is_array($item)) {
        $this->addItems($item);
      } else {
        $this->addItem($item);
      }
    }
  }

  public function addItem($item) {
    if (!$item instanceof $this->modelClass)
      throw new Exception('nur '.$this->modelClass.' in der Collection erlaubt');

    $item->setLevel($this->level+$this->indentItems); // ist eigentlich nur zu debug zwecken, da es bei getContent() nochmal gesetzt wird
    $this->items[] =& $item;
  }

  /**
   * 
   * @return string
   */  
  public function getContent() {
    $str = NULL;

    try {
      foreach ($this->items as $object) {
        $object->setLevel($this->level+$this->indentItems);

        $str .= (string) $object;
        //$str .= $this->lb();
      }
      return $str;
    } catch (Exception $e) {
      print $e;
    }
  }
}
?>