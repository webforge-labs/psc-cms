<?php

namespace Psc\XML;

use Psc\JS\jQuery;

class Scraper extends \Psc\SimpleObject {
  
  public function table($source, $tableSelector = 'table:eq(0)') {
    return new TableScraper($this->createJQuery($tableSelector, $source));
  }
  
  protected function createJQuery($selector, $source) {
    return new jQuery($selector,$source);
  }
}
?>