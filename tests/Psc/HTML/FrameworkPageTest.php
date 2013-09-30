<?php

namespace Psc\HTML;

class FrameworkPageTest extends \Webforge\Code\Test\Base implements \Webforge\Code\Test\HTMLTesting {
  
  public function setUp() {
    $this->chainClass = 'Psc\\HTML\\FrameworkPage';
    parent::setUp();

    $this->page = new FrameworkPage();
  }

  public function testAddCMSDefaultCSS_AddsCSS() {
    $this->page->addCMSDefaultCSS();

    $this->html = $this->page->html();

    $this->hasCSS('/css/reset.css');
    $this->hasCSS('/css/default.css');
    $this->hasCSS('/psc-cms-js/vendor/jquery-ui/css/smoothness/jquery-ui-1.8.22.custom.css');
    $this->hasCSS('/psc-cms-js/vendor/jqwidgets/styles/jqx.ui-smoothness.css');
  }

  public function testAddCMSRequireJSInDifferentModes() {
    $this->page->addCMSRequireJS($assetModus = 'development');

    $this->html = $this->page->html();

    $this->hasJS('/psc-cms-js/lib/config.js');
    $this->hasJS('/psc-cms-js/vendor/require.js')->attribute('data-main', $this->equalTo('/js/boot.js'));
  }

  public function testAddCMSRequireJSInBuiltMode() {
    $this->page->addCMSRequireJS($assetModus = 'built');

    $this->html = $this->page->html();

    // make sure, that this is copied in build from grunt
    $this->hasJS('/js-built/require.js')->attribute('data-main', $this->equalTo('/js-built/boot.js'));; 
  }

  protected function hasCSS($url) {
    $this->css('head link[rel="stylesheet"][href="'.$url.'"]')->count(1);
  }

  protected function hasJS($url) {
    return $this->css('script[type="text/javascript"][src="'.$url.'"]')->count(1);
  }
}
