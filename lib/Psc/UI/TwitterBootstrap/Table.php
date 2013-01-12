<?php

namespace Psc\UI\TwitterBootstrap;

class Table extends \Psc\UI\Table {
  
  //success 	Indicates a successful or positive action.
  //error 	Indicates a dangerous or potentially negative action.
  //warning 	Indicates a warning that might need attention.
  //info
  
  public function __construct() {
    parent::__construct();
    $this->html->addClass('table');
  }
  
  
  public function makeStriped() {
    $this->html->addClass('table-striped');
    return $this;
  }
}
?>