<?php

namespace Psc\HTML;

abstract class Base extends \Psc\SimpleObject implements \Psc\HTML\HTMLInterface {
  
  protected $html;
  
  protected $init = FALSE;

  abstract protected function doInit();
  
  public function init() {
    if (!$this->init) {
      $this->init = TRUE; // init erst true setzen, damit rekursive aufrufe weniger passieren
      $this->doInit();
    }
    return $this;
  }
  
  public function getContent() {
    $this->init();
    
    return $this->html->getContent();
  }
  
  public function html() {
    $this->init();
    
    return $this->html;
  }
  

  /**
   * @return string
   */
  public function __toString() {
    try {
      return (string) $this->html();
    /* wir duerfen hier eh keine Exception schmeissen und die Anwendung hält, so oder so
       so sehen wir wenigstens die Exception und verursachen das gleiche Verhalten, wie wenn wir sie nicht catchen
    */
    } catch (\Exception $e) {
      print $e;
      \Psc\PSC::terminate();
    }
    
    return '';
  }
  
  protected function convertHTMLName($name) {
    return \Psc\Form\HTML::getName($name);
  }

  public function isInit() {
    return $this->init;
  }
}
