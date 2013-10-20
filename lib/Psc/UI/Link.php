<?php

namespace Psc\UI;

use Psc\HTML\Base;

class Link extends \Psc\HTML\Base implements \Webforge\Types\Interfaces\Link {
  
  /**
   * Die Beschreibung des Links
   * @var string
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
  public function __construct($uri, $label = NULL) {
    $this->uri = $uri;
    $this->label = $label;
  }
  
  protected function doInit() {
    $this->html = HTML::tag('a',HTML::esc($this->label), array('href'=>$this->uri));
  }
  
  /**
   * @param string $label
   * @chainable
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }
  
  /**
   * @param string $uri
   * @chainable
   */
  public function setURI($uri) {
    $this->uri = $uri;
    return $this;
  }

  /**
   * @return string
   */
  public function getURI() {
    return $this->uri;
  }
}
?>