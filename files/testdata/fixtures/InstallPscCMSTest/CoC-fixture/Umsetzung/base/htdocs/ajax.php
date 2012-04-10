<?php
use \Psc\PSC;

$cms = PSC::getCMS();
$cms->init();
$cms->auth();

$ctrl = $cms->getAjaxController();

$ctrl->run();
?>