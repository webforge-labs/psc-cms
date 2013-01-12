<?php

namespace Psc\UI\Bits;

use Psc\HTML\HTMLInterface;

interface TextContent {
  
  /**
   * Returns the Text written in markup language (not HTML)
   * 
   * @return string
   */
  public function getMarkup();
}
?>