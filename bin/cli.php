#!/usr/bin/env php
<?php

namespace Psc;

require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'bootstrap.php';

$project = $GLOBALS['env']['container']->getProject();
$console = new \Psc\System\Console\Console($application = NULL, $doctrineModule = NULL, $project);
$console->run();
?>