<?php

class FluidGrid_Container extends FluidGrid_Collection {
  /**
   * Die Breite des Grids (Standard 16)
   * @var int
   */
  protected $width = 16;

  protected $indentItems = 1;

  public function addItem($row) {
    $row->setContainer($this);

    parent::addItem($row);
    return $this;
  }

  public function __toString() {
    $ret = $this->indent().'<div class="container_'.((int) $this->width).'">'.$this->lb();
    $ret .= (string) $this->getContent();
    $ret .= $this->indent().'</div>'.$this->lb();
    return $ret;
  }
}
?>