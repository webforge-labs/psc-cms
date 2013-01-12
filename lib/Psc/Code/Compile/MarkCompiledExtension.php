<?php

namespace Psc\Code\Compile;

use Psc\Code\Generate\GClass;

class MarkCompiledExtension extends \Psc\Code\Compile\Extension {
  
  public function compile(GClass $gClass, $flags = 0x000000) {
    $c = 'Psc\Code\Compile\Annotations\Compiled';
    if (!$gClass->hasDocBlock() || !$gClass->getDocBlock()->hasAnnotation($c)) { // @FIXME hasAnnotation kann falsch sein!
      
      /* erstelle einen DocBlock / modifiziere den DocBlock von gClass */
      if (!$gClass->hasDocBlock()) {
        $gClass->createDocBlock();
      }

      $gClass->getDocBlock()->addAnnotation($c::create());
    }
  }
}
?>