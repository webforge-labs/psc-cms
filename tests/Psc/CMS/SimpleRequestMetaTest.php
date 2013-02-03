<?php

namespace Psc\CMS;

class SimpleRequestMetaTest extends \Webforge\Code\Test\Base {
  
  protected $rm;
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\SimpleRequestMeta';
    parent::setUp();
    
    $this->rm = new SimpleRequestMeta(RequestMetaInterface::GET, '/entities/tag/7');
  }
  
  public function testSimpleRequestMetaCanBeConvertedToString() {
    $string = (string) $this->rm;
    $this->assertContains('/entities/tag/7', $string);
    $this->assertContains('GET', $string);
  }
}
?>