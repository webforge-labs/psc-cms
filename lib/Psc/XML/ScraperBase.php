<?php

namespace Psc\XML;

use Psc\JS\jQuery;
use Closure;

abstract class ScraperBase extends \Psc\SimpleObject {
  
  /**
   * @var Closure[]
   */
  protected $hooks = array();
  
  protected $helpers = array();
  
  abstract public function scrape();
  
  public function __construct(Array $defaultHooks) {
    $this->hooks = $defaultHooks;
  }
  
  protected function setHook($name, Closure $hook = NULL) {
    if (isset($hook))
      $this->hooks[$name] = $hook;
    return $this;
  }
  
  /**
   *
   * Usage:
   * extract($this->hooks());
   *
   * $myDefinedHook('bla','blubb');
   */
  protected function help() {
    if (!isset($this->helpers['jq'])) {
      $this->helpers['jq'] = function ($selector, $source = NULL) {
        if ($source) {
          return new jQuery($selector, $source);
        } else {
          return new jQuery($selector);
        }
      };
    }
    
    return array_merge($this->helpers, $this->hooks);
  }
}
?>