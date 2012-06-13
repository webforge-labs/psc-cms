<?php

namespace Psc\CSS;

/**
 * @group class:Psc\CSS\CSS
 */
class CSSTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $cSS;
  
  public function setUp() {
    $this->chainClass = 'Psc\CSS\CSS';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $manager = CSS::getManager();
    
    $this->assertSame($manager, CSS::register('/some/test/file.css','testfile'));
    $this->assertSame($manager, CSS::enqueue('testfile'));
    
    ob_start();
    CSS::load();
    $this->html = ob_get_contents();
    ob_end_clean();
    
    $this->test->css('link[rel=stylesheet][href="/css/some/test/file.css"]')->count(1);
    
    CSS::unregister('testfile');
    
    ob_start();
    CSS::load();
    $this->html = ob_get_contents();
    ob_end_clean();

    $this->test->css('link[rel=stylesheet][href="/css/some/test/file.css"]')->count(0);
  }
}
?>