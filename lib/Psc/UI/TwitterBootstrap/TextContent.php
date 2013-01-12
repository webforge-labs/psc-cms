<?php

namespace Psc\UI\TwitterBootstrap;

class TextContent extends BaseBit implements \Psc\UI\Bits\TextContent {
  
  /**
   * @var string
   */
  protected $markup;
  
  public function __construct($markup) {
    $this->markup = $markup;
  }
  
  public function getMarkup() {
    return $this->markup;
  }
}
?>