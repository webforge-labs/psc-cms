<?php

namespace Psc;

use \PHPUnit_Framework_TestCase,
    \DomDocument
;

/**
 * @deprecated whenever u can. Use $this->test bzw FrontendCodeTester (der rockt mehr)
 */
if (class_exists('PHPUnit_Framework_TestCase',FALSE)) {
  
  class PHPUnit_HTMLTester extends \Psc\Object {
    
    protected $case;
    
    protected $html;
    protected $debugVarName;
    
    protected $dom;
    
    protected $init;
    
    public function __construct(PHPUnit_Framework_TestCase $case, $html, $var = 'html') {
      $this->case = $case;
      $this->html = $html;
      $this->debugVarName = $var;
      
      $this->case->assertNotEmpty($this->html);
      $this->dom = new DomDocument;
      
      $this->init();
    }
    
    public function init() {
      libxml_use_internal_errors(TRUE);
      $this->dom->loadHTML($this->html);
    
      foreach (libxml_get_errors() as $error) {
        throw new \Psc\Exception('Dom Parsing libxml-Error: '.$error);
      }

      $this->init = true;
    }
    
    /**
     * Achtung, dies kann keine unterelemente zählen
     */
    public function has($cssSelector) {
      $this->case->assertTrue($this->init,'im HTMLTester muss erst init() ausgeführt werden');
      $this->case->assertSelectCount($cssSelector,TRUE,$this->dom,sprintf("Failed asserting that %s->has('%s');",'$'.$this->debugVarName,$cssSelector),TRUE);
    }
    
    public function assertHas($cssSelector) {
      return $this->has($cssSelector);
    }

    public function assertLength($cssSelector, $count = 1) {
      return $this->length($cssSelector, $count);
    }
    
    public function length($cssSelector, $count = 1) {
      $this->case->assertTrue($this->init,'im HTMLTester muss erst init() ausgeführt werden');
      $this->case->assertSelectCount($cssSelector,$count,$this->dom,sprintf("Failed asserting that %s->length('%s') == %d;",'$'.$this->debugVarName,$cssSelector,$count),TRUE);
    }
  }
}

?>