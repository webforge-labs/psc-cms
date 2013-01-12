<?php

namespace Psc\UI\TwitterBootstrap;

/**
 * 
 */
class Heading extends BaseBit implements \Psc\UI\Bits\Heading {
  
  /**
   * @var Psc\UI\TwitterBootstrap\TextContent
   */
  protected $content;
  
  public function __construct(TextContent $content) {
    $this->setContent($content);
  }
  
  protected function doInit() {
  }
  
  public function getContent() {
    return $this->content;
  }
  
  /**
   * @param Psc\UI\TwitterBootstrap\TextContent $content
   */
  public function setContent(TextContent $content) {
    $this->content = $content;
    return $this;
  }
}
?>