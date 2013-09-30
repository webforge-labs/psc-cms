<?php

namespace Psc\HTML;

/**
 * Eine HTML5 Page
 *
 */
class Page5 extends FrameworkPage {
  
  protected $doctype = '<!DOCTYPE html>';
  
  public function __construct() {
    parent::__construct();
    
    $this->setMeta(NULL, FALSE)->setAttribute('charset',$this->charset);
    
    $this->html->removeAttribute('xml:lang');
    $this->removeMeta('content-type'); // warum gibts das eigentlich nich in html5?
    $this->removeMeta('content-language');
    
    $this->setMeta('viewport', "width=device-width, initial-scale=1.0");
    
    $this->loadConditionalJS('/psc-cms-js/vendor/afarkas/html5shiv.min.js', 'lt IE 9');
  }
}
