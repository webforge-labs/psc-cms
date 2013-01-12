<?php

namespace Psc\UI\TwitterBootstrap;

class Link extends BaseBit implements \Psc\UI\Bits\Link {
  
  /**
   * Die Beschreibung des Links
   * @var Psc\UI\TwitterBootstrap\TextContent
   */
  protected $label;
  
  /**
   * Die URI des Links
   * @var string
   */
  protected $uri;
  
  /**
   * Erstellt einen neuen Link
   */
  public function __construct($uri, TextContent $label = NULL) {
    $this->uri = $uri;
    $this->label = $label;
  }
  
  /**
   * @param Psc\UI\TwitterBootstrap\Label $label
   * @chainable
   */
  public function setLabel(Label $label) {
    $this->label = $label;
    return $this;
  }

  /**
   * @return Psc\UI\TwitterBootstrap\Label
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * @param string $uri
   * @chainable
   */
  public function setUri($uri) {
    $this->uri = $uri;
    return $this;
  }

  /**
   * @return string
   */
  public function getUri() {
    return $this->uri;
  }
  
  public function getUrl() {
    return $this->getUri();
  }
}
?>