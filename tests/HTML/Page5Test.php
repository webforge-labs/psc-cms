<?php

namespace Psc\HTML;

/**
 * @group class:Psc\HTML\Page5
 */
class Page5Test extends \Psc\Code\Test\HTMLTestCase {
  
  protected $page5;
  
  public function setUp() {
    $this->chainClass = 'Psc\HTML\Page5';
    parent::setUp();
    $this->page5 = new Page5();
    $this->html = $this->page5->html();
  }
  
  public function testDoctype() {
    $this->stringStartsWith('<!DOCTYPE html>', $this->html);
  }
  
  public function testHTML5FixForIELTE8() {
    $this->assertContains('<!--[if lte IE 8]><script src="/js/html5-ie-fix.js"></script><![endif]-->', $this->test->css('head')->html());
  }
  
  public function testHTMLXMLNS() {
    // kein xml:lang
    $this->assertContains('<html xmlns="http://www.w3.org/1999/xhtml">', (string) $this->html);
  }

  public function testCharset() {
    // minimalistisches meta charset
    $html = $this->test->css('head meta[charset="utf-8"]')->count(1)->hasNotAttribute('http-equiv')->hasNotAttribute('name');
  }
}
?>