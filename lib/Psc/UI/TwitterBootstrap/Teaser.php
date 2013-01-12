<?php

namespace Psc\UI\TwitterBootstrap;

/**
 * 
 */
class Teaser extends BaseBit implements \Psc\UI\Bits\Teaser {
  
  /**
   * @var Psc\UI\TwitterBootstrap\Heading
   */
  protected $heading;
  
  /**
   * @var Psc\UI\TwitterBootstrap\TextContent
   */
  protected $content;
  
  /**
   * @var Psc\UI\TwitterBootstrap\Link
   */
  protected $link;
  
  public function __construct(Heading $heading, TextContent $content, Link $link = NULL) {
    $this->setHeading($heading);
    $this->setContent($content);
    
    if (isset($link))
      $this->setLink($link);
  }
  
  /**
   * @return Psc\UI\Bits\Heading
   */
  public function getHeading() {
    return $this->heading;
  }
  
  /**
   * @param Psc\UI\TwitterBootstrap\Heading $heading
   */
  public function setHeading(Heading $heading) {
    $this->heading = $heading;
    return $this;
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
  
  /**
   * @return Psc\UI\Bits\Link|NULL
   */
  public function getLink() {
    return $this->link;
  }
  
  /**
   * @param Psc\UI\TwitterBootstrap\Link $link
   */
  public function setLink(Link $link) {
    $this->link = $link;
    return $this;
  }
}
?>