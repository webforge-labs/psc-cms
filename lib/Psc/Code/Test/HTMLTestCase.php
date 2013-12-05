<?php

namespace Psc\Code\Test;

use Psc\HTML\HTMLInterface;

class HTMLTestCase extends \Psc\Code\Test\Base implements \Webforge\Code\Test\HTMLTesting {
  
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
  public function setDebugContextHTML(\Webforge\Code\Test\CSSTester $css, $html, $selectorInfo) {
    $this->debugContextHTML = $html;
    $this->debugContextLabel = 'selector: '.$selectorInfo;
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
