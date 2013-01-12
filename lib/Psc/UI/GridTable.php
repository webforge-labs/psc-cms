<?php

namespace Psc\UI;

class GridTable extends ClassedTable {
  
  public function __construct() {
    parent::__construct();
    
    $this->html->addClass('\Psc\grid');
    $this->html->addClass('ui-widget');
    $this->html->addClass('ui-widget-content');
  }
  
  protected function onFirstTR(HTMLTag $tr) {
    parent::onFirstTR($tr);
    $tr->addClass('ui-widget-header');
  }
}
?>