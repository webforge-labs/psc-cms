<?php

namespace Psc\HTML;

use Psc\HTML\Page;

/**
 * @group class:Psc\HTML\Page
 */
class PageTest extends \Psc\Code\Test\HTMLTestCase {

  public function testManagerInjection() {
    $jsManager = new \Psc\JS\Manager('fortest');
    $cssManager = new \Psc\CSS\Manager('fortest');
    $page = new Page($jsManager, $cssManager);
    
    $this->assertInstanceOf('Psc\HTML\HTMLInterface', $page);
    $this->assertSame($jsManager,$page->getJSManager());
    $this->assertSame($cssManager,$page->getCSSManager());
    
    $this->assertNotSame($jsManager, \Psc\JS\JS::getManager());
  }  

  public function testSetLanguage() {
    $jsManager = new \Psc\JS\ProxyManager('fortest');
    $cssManager = new \Psc\CSS\Manager('fortest');

    $page = new Page($jsManager, $cssManager);
    $page->setLanguage('fr');

    $this->html = $page->html();

    $this->assertContains(
      '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">',
      (string) $this->html
    );

    $this->test->css('head meta[name="content-language"]')->hasAttribute('content', 'fr');
  }
}
?>