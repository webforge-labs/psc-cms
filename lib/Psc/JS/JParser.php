<?php

namespace Psc\JS;

import('PLUG.JavaScript.JParser');
import('PLUG.JavaScript.JLex');
import('PLUG.JavaScript.JTokenizer'); 

class JParser extends \JParser {
  
  public static function dumpNode($node) {
    ob_start();
    $node->dump(new \JLex);
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
  }
  
}
?>