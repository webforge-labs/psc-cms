<?php

use Psc\Code\Test\HTMLTestCase;

class HomePageAcceptanceTest extends HTMLTestCase {

  public function testHomePageIsDisplayedWithSlideshowAndTeasers() {
    $this->html = $this->getHTMLForHomnePage();
    
    // fail as early as possible
    $this->test->css('html')->count(1)
      ->test('body')->count(1);

    $this->assertNavigation();
    $this->assertSlideShow();
    $this->assertFooter();
    $this->assertSidebar(array('empty'=>true));
  }

  protected function assertSlideShow() {
    // this sets the debug context to html from #main
    $this->test->css('#main')->count(1)
      ->css('div.carousel')->count(1)
      ->css('slideset')->count(1)
        ->css('slide')->count(6)->end()
        ->css('slide:eq(0)')->count(1)->css('h2')->hasText('Headline1')->end()
    ;
  }

  protected function assertNavigation() {
    $this->test->css('body #header div.nav')->count(1) 
      ->css('div.nav-wrapper ul.nav-main')->count(1)->asContext() // this sets the debug context to the contents of the ul of navigation
        ->css('li:eq(0) a')->containsText('About us')->end()
    ;
  }

  protected function assertFooter() {
    // this sets the debug context to html from #footer
    $this->test->css('#footer')
      ->css('div.logo')->exists();
  }

  protected function assertSidebar() {
    $this->test->css('#sidebar')
      ->css('div.teaser')->atLeast(1)
    ;
  }
}
