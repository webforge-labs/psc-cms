<?php

namespace Psc\HTML;

/**
 * Eine HTML5 Page
 *
 */
class Page5 extends Page {
  
  protected $doctype = '<!DOCTYPE html>';
  
  public function __construct(\Psc\JS\Manager $jsManager = NULL, \Psc\CSS\Manager $cssManager = NULL) {
    $jsManager = $jsManager ?: new \Psc\JS\ProxyManager();
    parent::__construct($jsManager, $cssManager);
    $this->setMeta(NULL, FALSE)->setAttribute('charset',$this->charset);
    
    $this->html->removeAttribute('xml:lang');
    $this->removeMeta('content-type'); // warum gibts das eigentlich nich in html5?
    $this->removeMeta('content-language');
    
    
    $this->setMeta('viewport', "width=device-width, initial-scale=1.0");
    
    $this->head->content['/js/html5.js'] = '<!--[if lte IE 8]><script src="/js/html5-ie-fix.js"></script><![endif]-->';
  }
}
?>