<?php

namespace Psc\XML;

use Psc\JS\jQuery;

class Scraper extends \Psc\SimpleObject {
  
  public function table($source, $tableSelector = 'table:eq(0)') {
    if (!($source instanceof jQuery)) {
      $source = $this->createJQuery($tableSelector, $source);
    }
    
    return new TableScraper($source);
  }
  
  protected function createJQuery($selector, $source) {
    return new jQuery($selector,$source);
  }
}
?>