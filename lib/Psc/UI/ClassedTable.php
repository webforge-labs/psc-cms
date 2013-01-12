<?php

namespace Psc\UI;

/**
 *
 * Options:
 * siehe: OptionsObject
 *
 * css.extendedClasses  fügt mega lange eindeutige css Klassen hinzu. Z.b. für "first" "$namespace-ui-table-row-first" usw
 */
class ClassedTable extends Table {
  
  public function __construct() {
    parent::__construct();
    
    $this->html->addClass('\Psc\table');
  }
  
  protected function createTD(HTMLTag $tr) {
    $td = parent::createTD($tr);
    
    if ($this->getOption('css.extendedClasses',FALSE))
      $td->addClass('\Psc\ui-table-column');
    
    return $td;
  }

  protected function createTR() {
    $tr = parent::createTR();

    if ($this->getOption('css.extendedClasses',FALSE))
      $tr->addClass('\Psc\ui-table-row');
    
    return $tr;
  }
  
  protected function onFirstTR(HTMLTag $tr) {
    $tr->addClass('first');
    if ($this->getOption('css.extendedClasses',FALSE))
      $tr->addClass('\Psc\ui-table-row-first');
  }
  
  protected function createTH(HTMLTag $tr) {
    $th = parent::createTH($tr);
    
    if ($this->getOption('css.extendedClasses',FALSE))
      $th->addClass('\Psc\ui-table-header');
    
    return $th;
  }
  
  protected function onLastTD(HTMLTag $td) {
    $td->addClass('last');
    
    if ($this->getOption('css.extendedClasses',FALSE))
      $td->addClass('\Psc\ui-table-column-first');
  }
  
  protected function onLastTH(HTMLTag $th) {
    $this->onLastTD($th);
  }

  protected function onFirstTH(HTMLTag $th) {
    $this->onFirstTD($th);
  }

  protected function onFirstTD(HTMLTag $td) {
    $td->addClass('first');
    
    if ($this->getOption('css.extendedClasses',FALSE))
      $td->addClass('\Psc\ui-table-column-last');
  }
  
}