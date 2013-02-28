<?php

namespace Psc\Net\Service;

/**
 * 
 */
class LinkRelation {
  
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var string
   */
  protected $href;
  
  public function __construct($name, $href) {
    $this->setName($name);
    $this->setHref($href);
  }
  
  /**
   * @param string $name
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * @param string $href
   */
  public function setHref($href) {
    $this->href = $href;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getHref() {
    return $this->href;
  }
}
?>