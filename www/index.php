<?php

use Psc\CMS\ProjectMain;
use Psc\PSC;
use Psc\TPL\Template;
use Psc\JS\Helper as js;
use Psc\HTML\FrameworkPage;

class TestMain extends \Psc\CMS\ProjectMain {
  
  public function createHTMLPage() {
    $page = new FrameworkPage();
    $page->setTitleForProject($this->getProject());
    $page->addCMSDefaultCSS();
    $page->addRequireJS();
    //$page->addTwitterBootstrapCSS();
    $page->loadCSS('/psc-cms-js/css/twitter-grid.css');
    return $page;
  }
}

$navTest = new Template(array('test','navigation'), array(), 'Seiten verarbeiten (lahm)');
$navTest = new Template(array('test','navigation.fast'), array(), 'Seiten verarbeiten (schnell)');
$pagesTest = new Template(array('test','pages'), array(), 'Seiten verwalten');
$widgetsTest = new Template(array('test','sce-widgets'), array(), 'Widgets-Test');

/* Controll */
$main = new TestMain();
$main->init();
$main->auth();

$tabs = $main->getContentTabs();
$tabs->addTabOpenable($widgetsTest);
$tabs->select(1);

/* View */
$page = $main->getMainHTMLPage();

print $page->getHTML();
?>