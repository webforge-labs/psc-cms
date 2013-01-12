<?php

namespace Psc\Code\Build;

class Snippets extends \Psc\Object {
  
  protected $snippets;
  
  public function __construct(Array $snippets = array()) {
    $this->snippets = array();
    foreach ($snippets as $name => $code) {
      $this->set($name,$code);
    }
  }
  
  public function replace($code) {
    $search = array();
    $repl = array();
    foreach ($this->snippets as $name => $snippet) {
      $search[] = '/*%%'.mb_strtoupper($name).'%%*/';
      $repl[] = $snippet;
    }

    /* replace in code */
    return str_replace($search, $repl, $code);
  }
  
  public function set($snippetName, $code) {
    $this->snippets[mb_strtoupper($snippetName)] = $code;
  }
  
  /**
   * @return string
   */
  public function get($snippetName) {
    $snippetName = mb_strtoupper();
    return array_key_exists($snippetName, $this->snippets) ? $this->snippets[$snippetName] : NULL;
  }
}
?>