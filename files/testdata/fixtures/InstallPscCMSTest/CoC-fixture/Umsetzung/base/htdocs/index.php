<?php

namespace CoC;

use \Psc\PSC;

/* Bootstrap */
$cms = PSC::getCMS();
$cms->init();
$cms->auth();

/* Controll */
$rc = new CMS\RightContent();
$rc->populateLists($cms);

/* View */
$html = new HTMLPage(); 
$html->setOpen();
$html->addMarkup($cms);
print $html->getHTML();

?>