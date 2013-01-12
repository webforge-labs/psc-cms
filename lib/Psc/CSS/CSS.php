<?php

namespace Psc\CSS;

class CSS {
  
  /**
   * @return CSSManager
   */
  public static function getManager() {
    return Manager::instance('default');
  }
}
?>