<?php

namespace Psc\JS;

require_once 'JParser'.DIRECTORY_SEPARATOR.'autoload.php';

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