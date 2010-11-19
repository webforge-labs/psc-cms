<?php


class FluidGrid_String extends FluidGrid_Model {

  protected $string;

  public function __construct($string) {
    $this->string = $string;
  }

  public function getContent() {
    return $this->string;
  }

  public function __toString() {
    return (string) $this->string;
  }
}

?>