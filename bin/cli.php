<?php

namespace Psc;

require 'bootLoader.php';

$bootLoader = new BootLoader();


$bootLoader->init();


PSC::getProject()->setTests(TRUE);

$console = new \Psc\System\Console\Console();
return $console->run();
?>