<?php

namespace Psc\Code\Compile;

use Psc\Code\Generate\GClass;

interface ClassCompiler {
  
  /**
   * Compiled / Verändert die Klasse $gClass 
   *
   */
  public function compile(GClass $gClass, $flags = 0x000000);

}
?>