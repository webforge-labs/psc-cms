<?php

namespace Psc\Code\Test;

class HTMLTestCase extends \Psc\Code\Test\Base {
  
  protected $html;
  
  protected function onNotSuccessfulTest(\Exception $e) {
    if (isset($this->html)) {
      print '------------ HTML-debug ------------'."\n";
      print $this->html;
      print '------------ /HTML-debug -----------'."\n";
    }
    
    throw $e;
  }
  
  public function getHTML() {
    return $this->html;
  }
  
  public function setHTML($html) {
    $this->html = $html;
    return $this;
  }
}
?>