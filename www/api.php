<?php

namespace Psc;

use Psc\CMS\ProjectMain;

$main = new ProjectMain();
$main->init();
$main->auth();

$main->handleAPIRequest();
?>