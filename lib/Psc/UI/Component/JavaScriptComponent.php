<?php

namespace Psc\UI\Component;

interface JavaScriptComponent {
  
  /**
   * @return Psc\JS\Snippet
   */
  public function getJavascript();
  
}
?>