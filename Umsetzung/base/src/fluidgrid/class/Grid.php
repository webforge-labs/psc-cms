<?php

/**
 * Die Grids sind Bestandteile einer Row
 * 
 */
class FluidGrid_Grid extends FluidGrid_Collection {

  protected $indentItems = 1;

  /**
   * Die Feste Größe des Grids
   * 
   * Ist die Größe nicht gesetzt, wird von $this->parent die Größe errechnet
   * @var int|NULL
   */
  protected $width;

  /**
   * Verwaltet die Größe des Grids
   * @var FluidGrid_IGridContainer
   */
  protected $parent;

  /**
   * 
   * @var FluidGrid_HTMLElement
   */
  protected $div;

  public function __construct() {
    
    $this->div = new FluidGrid_HTMLElement('div');

    $args = func_get_args();
    parent::__construct($args);
  }
  
  public static function factory() {
    $args = func_get_args();
    
    return new FluidGrid_Grid($args);
  }

  public function setParent(FluidGrid_IGridContainer $parent) {
    $this->parent = $parent;
    return $this;
  }

  public function __call($function, $args = NULL) {
    try {
      return parent::__call($function, $args);
    } catch (BadMethodCallException $e) {
      FluidGrid_Framework::call_func(array($this->div,$function),$args);
      return $this;
    }
  
    throw new BadMethodCallException('Call to undefined method '.get_class($this).'::'.$method.'()');
  }

  public function __toString() {
    try {
      $w = (isset($this->parent)) ? $this->parent->getGridWidth($this) : $this->getWidth();
    
      $this->div
          ->setClass('grid_'.$w)
          ->setLevel($this->level)
          ->setContent(FluidGrid_Collection::factory($this->getItems())->setIndentItems(1));

      return (string) $this->div;
    } catch (Exception $e) {
      print $e;
    }
  }
}

?>