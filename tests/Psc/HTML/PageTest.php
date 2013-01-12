<?php

namespace Psc\HTML;

use Psc\HTML\Page;

/**
 * @group class:Psc\HTML\Page
 */
class PageTest extends \Psc\Code\Test\Base {

  public function testManagerInjection() {
    $jsManager = new \Psc\JS\Manager('fortest');
    $cssManager = new \Psc\CSS\Manager('fortest');
    $page = new Page($jsManager, $cssManager);
    
    $this->assertInstanceOf('Psc\HTML\HTMLInterface', $page);
    $this->assertSame($jsManager,$page->getJSManager());
    $this->assertSame($cssManager,$page->getCSSManager());
    
    $this->assertNotSame($jsManager, \Psc\JS\JS::getManager());
  }  
}
?>