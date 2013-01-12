<?php

namespace Psc\HTML;

class Debugger extends \Psc\SimpleObject {
  
  protected $colors = array('red','blue','yellow','green','orange');
  
  protected $index = 0;
  
  public function container(Tag $container) {
    $container->setStyle('border', '1px solid '.$this->nextColor());
    return $this;
  }
  
  public function nextColor() {
    return $this->colors[$this->index++];
  }
  
  public function reset() {
    $this->index = 0;
    return $this;
  }
}
?>