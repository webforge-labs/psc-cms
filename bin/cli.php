<?php

namespace Psc;

require __DIR__.DIRECTORY_SEPARATOR.'psc-cms.phar.gz';
require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'bootstrap.php';

PSC::getProject()->setTests(TRUE);

$console = new \Psc\System\Console\Console();
$console->run();
?>