<?php

namespace CoC\CMS;

class RightContent extends \Psc\Object implements \Psc\CMS\DropContentsListPopulator {
  
  public function populateLists(\Psc\CMS\CMS $cms) {
    $lists[] = $cms->newDropContentsList('Geburtstage');
  }
  
}
?>