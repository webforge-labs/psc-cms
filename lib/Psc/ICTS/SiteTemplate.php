<?php

namespace Psc\ICTS;

class SiteTemplate extends HTMLTemplate {
  
  public function __construct(Binder $binder) {
    $this->root = TRUE;
    
    $this->binder = $binder;
    parent::__construct($this,'site');
  }
  
}


?>