<?php

use Psc\CMS\ProjectMain;
use Psc\PSC;
use Psc\TPL\Template;
use Psc\JS\Helper as js;

$navTest = new Template(array('test','navigation'), array(), 'Seiten verarbeiten (lahm)');
$navTest = new Template(array('test','navigation.fast'), array(), 'Seiten verarbeiten (schnell)');
$pagesTest = new Template(array('test','pages'), array(), 'Seiten verwalten');

/* Controll */
$main = new ProjectMain();
$main->init();
$main->auth();

$tabs = $main->getContentTabs();
$tabs->addTabOpenable($pagesTest);
$tabs->select(1);

/* View */
$page = $main->getMainHTMLPage();

print $page->getHTML();
?>