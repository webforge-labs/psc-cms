<?php

namespace Psc\JS;

interface CodeGatherer {
  
  /**
   * @param string[] dependencies von Joose Klassen
   */
  public function gatherJSCode($jsCode, Array $dependencies);
  
}
?>