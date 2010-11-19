<?php

class fg extends FluidGrid_Object {

  public static function htmlElement($tag, FluidGrid_Model $innerHTML = NULL, Array $attributes = NULL) {
    return FluidGrid_HTMLElement::factory($tag, $innerHTML, $attributes);
  }
  
  public static function grid() {
    /* aus performance könnte man hier auch den code von factory kopieren, da sollte ja eh nichts spannendes passieren */
    $args = func_get_args();
    return FluidGrid_Grid::factory($args);
  }

  public static function s($string) {
    return new FluidGrid_String($string);
  }

  public static function c(Array $collection = NULL) {
    return new FluidGrid_Collection($collection);
  }

  public static function h($tag, $innerHTML = NULL, $attributes = NULL) {
    return FluidGrid_HTMLElement::factory($tag, $innerHTML, $attributes);
  }

  public static function string($string) {
    return new FluidGrid_String($string);
  }

  public static function box($headline, $content, $toggle = TRUE) {
    return FluidGrid_Box::factory($headline, $content, $toggle);
  }


  /**
   * 
   * @return FluidGrid_Grid
   */
  public static function menu($menu) {
    return fg::grid(
      fg::box(
        'Section Menu',
        new FluidGrid_Menu($menu)
      )->addClass('menu')
    );
  }
}

?>