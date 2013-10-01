<?php

namespace Psc\Code\Test;

use Psc\HTML\HTMLInterface;

class CSSTester extends \Webforge\Code\Test\CSSTester implements HTMLInterface {
  
  /**
   * Startet einen neuen (Sub)Test mit find($selector)
   *
   * ein alias von css()
   * @discouraged
   */
  public function test($selector) {
    return $this->css($selector);
  }
}
