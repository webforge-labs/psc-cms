<?php

namespace Psc\UI;

class HTML extends \Psc\HTML\HTML {

  /**
   * @return HTMLTag
   * @see HTMLTag::__construct()
   */
  public static function tag($name, $content = NULL, Array $attributes = NULL, $flags = 0x000000) {
    $tag = new \Psc\UI\HTMLTag($name, $content, $attributes, $flags); // hier full wegen namespace wahnsinn
    return $tag;
  }

  public static function class2html($class) {
    return str_replace('\\', '_', trim($class,'\\'));
  }
  
  public static function html2class($html) {
    return str_replace('_', '\\', $html);
  }
}
?>