<?php

namespace Psc\Code\Test;

use Psc\HTML\HTMLInterface;

class HTMLTestCase extends \Psc\Code\Test\Base {
  
  protected $html, $debugContextHTML, $debugContextLabel = 'no-context';
  
  protected function onNotSuccessfulTest(\Exception $e) {
    if (isset($this->debugContextHTML)) {
      printf ('------------ HTML-debug (%s) ------------'."\n", $this->debugContextLabel);
      print $this->debugContextHTML instanceof HTMLInterface ? $this->debugContextHTML->html() : $this->debugContextHTML;
      printf ('------------ /HTML-debug (%s) -----------'."\n", $this->debugContextLabel);
    } elseif (isset($this->html)) {
      print '------------ HTML-debug ------------'."\n";
      print $this->html;
      print '------------ /HTML-debug -----------'."\n";
    }
    
    throw $e;
  }

  /**
   * Can be called to set the debug context
   */
  public function setDebugContextHTML($htmlly, $label) {
    $this->debugContextHTML = $htmlly;
    $this->debugContextLabel = $label;
    return $this;
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