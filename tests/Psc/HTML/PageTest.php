<?php

namespace Psc\HTML;

use Psc\HTML\Page;

/**
 * @group class:Psc\HTML\Page
 */
class PageTest extends \Psc\Code\Test\HTMLTestCase {

  public function setup() {
    parent::setup();

    $this->page = new Page();
  }

  public function testIsHTMLInterface() {
    $this->assertInstanceOf('Psc\HTML\HTMLInterface', $this->page);
  }

  public function testSetLanguage() {
    $this->page->setLanguage('fr');

    $this->html = $this->page->html();

    $this->assertContains(
      '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">',
      (string) $this->html
    );

    $this->test->css('head meta[name="content-language"]')->hasAttribute('content', 'fr');
  }

  public function testloadCSSAddsAHeadCSS() {
    $this->page->loadCSS('/css/bootstrap.css');

    $this->html = $this->page->html();

    $this->test->css('head link[href="/css/bootstrap.css"]')->count(1);
  }

  public function testloadCSSAddsAHeadCSS_NotTwice() {
    $this->page->loadCSS('/css/bootstrap.css');
    $this->page->loadCSS('/css/bootstrap.css');

    $this->html = $this->page->html();
    $this->page->loadCSS('/css/bootstrap.css');

    $this->html = $this->page->html();

    $this->test->css('head link[href="/css/bootstrap.css"]')->count(1);
  }
}
