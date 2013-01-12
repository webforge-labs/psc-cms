<?php

namespace Psc\UI\TwitterBootstrap;

use Psc\UI\Bits;

class Builder {

  /**
   * @return Psc\UI\Bits\TextContent
   */
  public function textContent($markup) {
    if ($markup instanceof TextContent) {
      return $markup;
    } else {
      return new TextContent($markup);
    }
  }
  
  public function heading($contentMarkup) {
    if ($contentMarkup instanceof Heading) {
      return $contentMarkup;
    } else {
      return new Heading($this->textContent($contentMarkup));
    }
  }
  
  public function link($url, $label) {
    return new Link($url, $this->textContent($label));
  }
  
  /**
   * @return Psc\UI\Bits\Teaser
   */
  public function teaser(Bits\Heading $heading, Bits\TextContent $content, Bits\Link $link = NULL) {
    return new Teaser($heading, $content, $link);
  }

}
?>